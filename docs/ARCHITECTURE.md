# HSMart — High Level Architecture Plan (Revisi)

**SaaS Sistem Manajemen Minimarket**  
Multi-tenant (single database), single outlet per tenant, subscription by duration, white-label.  
**Tenant ditentukan dari user login (tenant_id di users), BUKAN subdomain/domain.**

Stack: Laravel 12, Livewire 4, Flux UI, MySQL 8, Redis (optional).

---

## Karakteristik Sistem

1. **Multi-tenant** — single database.
2. **Tidak pakai subdomain/domain** — tenant dibedakan berdasarkan **user login** (`tenant_id` di `users`).
3. **Single outlet per tenant** — tidak ada multi outlet (struktur tetap bisa di-upgrade nanti).
4. **Subscription berbasis durasi** — semua fitur full, beda hanya masa aktif.
5. **White-label** — logo, warna, nama toko, footer struk per tenant.
6. **Business logic** — modular, Service layer.
7. **Event-driven** — untuk stock dan journal.
8. **Tidak over-engineered** — planning dulu, implementasi bertahap.

---

## 1. Struktur Folder Modular (Domain-Based)

```
app/
├── Domains/
│   ├── Tenant/
│   │   ├── Models/
│   │   │   ├── Tenant.php
│   │   │   └── TenantSetting.php
│   │   ├── Services/
│   │   │   └── TenantService.php
│   │   └── Middleware/
│   │       └── ResolveTenantFromAuth.php
│   │
│   ├── Subscription/
│   │   ├── Models/
│   │   │   └── Subscription.php
│   │   ├── Services/
│   │   │   └── SubscriptionService.php
│   │   └── Middleware/
│   │       └── CheckSubscription.php
│   │
│   ├── Product/
│   │   ├── Models/
│   │   │   └── Product.php
│   │   ├── Services/
│   │   │   └── ProductService.php
│   │   ├── Livewire/
│   │   │   ├── ProductIndex.php
│   │   │   ├── ProductForm.php
│   │   │   └── StockAdjustment.php
│   │   └── ...
│   │
│   ├── POS/
│   │   ├── Models/
│   │   │   ├── Sale.php
│   │   │   ├── SaleItem.php
│   │   │   └── Payment.php
│   │   ├── Services/
│   │   │   └── SaleService.php
│   │   ├── Livewire/
│   │   │   ├── PosCart.php
│   │   │   └── SaleList.php
│   │   └── Events/
│   │       └── SaleCompleted.php
│   │
│   ├── Purchasing/
│   │   ├── Models/
│   │   │   ├── Supplier.php
│   │   │   ├── Purchase.php
│   │   │   └── PurchaseItem.php
│   │   ├── Services/
│   │   │   └── PurchaseService.php
│   │   └── Events/
│   │       └── PurchaseCompleted.php
│   │
│   ├── Accounting/
│   │   ├── Models/
│   │   │   ├── Account.php
│   │   │   ├── Journal.php
│   │   │   └── JournalItem.php
│   │   ├── Services/
│   │   │   └── JournalService.php
│   │   └── Listeners/
│   │       └── (listener jurnal untuk SaleCompleted & PurchaseCompleted)
│   │
│   ├── Reporting/
│   │   ├── Services/
│   │   │   └── ReportService.php
│   │   └── Livewire/
│   │       └── (RingkasanOmzet, LaporanPenjualan, TopProduk, LaporanStok, LabaRugi)
│   │
│   └── Settings/
│       └── Livewire/
│           └── (WhiteLabelSettings — pakai TenantSetting dari domain Tenant)
│
├── Services/                    # Shared / cross-domain
│   └── StockService.php         # POS (kurangi), Purchasing (tambah), Product (adjustment)
│
├── Models/
│   └── User.php                 # Punya tenant_id; auth tetap di sini
│
├── Http/
├── Livewire/                    # Root (layout, auth) jika ada
└── ...
```

**Keputusan:**

- **Tenant resolution** — Hanya dari auth: `ResolveTenantFromAuth` baca `auth()->user()->tenant_id` → set context → helper `tenant()`.
- **ProductService** — CRUD product, validasi bisnis product; StockService hanya urusan quantity (increase/decrease/adjust).
- **ReportService** — Semua query laporan (omzet, penjualan, top produk, stok, laba rugi) terpusat; Livewire hanya panggil service.
- **StockService** di `app/Services/` — dipakai Product, POS, Purchasing; shared.
- Struktur sengaja single-outlet; kolom `tenant_id` konsisten agar nanti bisa ditambah `outlet_id` tanpa mengubah pola.

---

## 2. Daftar Migration (Urut sesuai Dependency)

Semua tabel bisnis wajib punya `tenant_id` (indexed). Tidak ada kolom domain/subdomain untuk tenant.

| # | Migration | Keterangan |
|---|-----------|------------|
| 1 | `create_tenants_table` | id, name, slug (unique), timestamps. Tanpa domain/subdomain. |
| 2 | `create_tenant_settings_table` | id, tenant_id (FK), store_name, logo_path, primary_color, secondary_color, receipt_footer, currency, timezone, timestamps |
| 3 | `create_subscriptions_table` | id, tenant_id (FK), started_at, ends_at, status (enum: trial, active, expired), **price**, **duration_days**, timestamps |
| 4 | `add_tenant_id_to_users_table` | tenant_id (FK, nullable untuk super-admin). Index tenant_id. |
| 5 | `create_accounts_table` | id, tenant_id (FK), code, name, type (enum), is_active, timestamps |
| 6 | `create_products_table` | id, tenant_id (FK), sku, barcode, name, cost_price, sell_price, stock, minimum_stock, is_active, timestamps. Index: tenant_id, unique(tenant_id, sku), unique(tenant_id, barcode) |
| 7 | `create_suppliers_table` | id, tenant_id (FK), name, contact, phone, address, timestamps |
| 8 | `create_sales_table` | id, tenant_id (FK), sale_number, sale_date, customer_name (nullable), total_amount, status, timestamps |
| 9 | `create_sale_items_table` | id, sale_id (FK), product_id (FK), qty, unit_price, subtotal, timestamps |
| 10 | `create_payments_table` | id, sale_id (FK), amount, method, paid_at, reference (nullable), timestamps |
| 11 | `create_purchases_table` | id, tenant_id (FK), supplier_id (FK), purchase_number, purchase_date, total_amount, status, timestamps |
| 12 | `create_purchase_items_table` | id, purchase_id (FK), product_id (FK), qty, unit_cost, subtotal, timestamps |
| 13 | `create_journals_table` | id, tenant_id (FK), number, date, description, reference_type, reference_id (nullable), timestamps |
| 14 | `create_journal_items_table` | id, journal_id (FK), account_id (FK), debit, credit, description (nullable), timestamps |

**Index wajib:**  
- Setiap tabel bisnis: `tenant_id`.  
- products: unique `(tenant_id, sku)`, unique `(tenant_id, barcode)`.  
- sales: unique `(tenant_id, sale_number)`.  
- journals: unique `(tenant_id, number)`.  
- subscriptions: `tenant_id`, `(tenant_id, ends_at)`.

---

## 3. Model dan Relasi Utama

| Model | Tabel | Relasi utama |
|-------|--------|----------------|
| **Tenant** | tenants | hasOne TenantSetting, hasMany Subscription, hasMany User |
| **TenantSetting** | tenant_settings | belongsTo Tenant |
| **Subscription** | subscriptions | belongsTo Tenant. Scope: current (status active, ends_at >= now) |
| **User** | users | belongsTo Tenant (tenant_id) |
| **Account** | accounts | belongsTo Tenant. hasMany JournalItem |
| **Product** | products | belongsTo Tenant. hasMany SaleItem, hasMany PurchaseItem |
| **Supplier** | suppliers | belongsTo Tenant. hasMany Purchase |
| **Sale** | sales | belongsTo Tenant. hasMany SaleItem, hasMany Payment |
| **SaleItem** | sale_items | belongsTo Sale, belongsTo Product |
| **Payment** | payments | belongsTo Sale |
| **Purchase** | purchases | belongsTo Tenant, belongsTo Supplier. hasMany PurchaseItem |
| **PurchaseItem** | purchase_items | belongsTo Purchase, belongsTo Product |
| **Journal** | journals | belongsTo Tenant. hasMany JournalItem. Polymorphic reference (Sale, Purchase) |
| **JournalItem** | journal_items | belongsTo Journal, belongsTo Account |

**Global scope:**  
Semua model bisnis (Product, Sale, Purchase, Account, Journal, Supplier, dll) pakai **BelongsToTenant**: otomatis `where('tenant_id', tenant()->id)`.  
Tenant & TenantSetting tidak pakai scope. User tidak pakai scope (tenant dari auth user).

---

## 4. Service Class

| Service | Lokasi | Tanggung jawab |
|---------|--------|----------------|
| **TenantService** | Domains/Tenant/Services | CRUD tenant, baca/update TenantSetting (white-label). Tidak ada resolve dari domain. |
| **SubscriptionService** | Domains/Subscription/Services | Cek status (trial/active/expired), mulai trial 7 hari saat tenant dibuat, perpanjang, canCreateSale() / canCreatePurchase(). |
| **ProductService** | Domains/Product/Services | CRUD product, validasi (sku/barcode unik per tenant). Panggil StockService untuk adjustment. |
| **StockService** | app/Services | decreaseStock(product, qty), increaseStock(product, qty), adjustStock(product, newQty). Dipanggil listener & ProductService. |
| **SaleService** | Domains/POS/Services | Create Sale + SaleItem + Payment, validasi stok, dispatch SaleCompleted. Tidak kurangi stok di sini (listener). |
| **PurchaseService** | Domains/Purchasing/Services | Create Purchase + PurchaseItem, dispatch PurchaseCompleted. Tidak tambah stok di sini (listener). |
| **JournalService** | Domains/Accounting/Services | createJournal(tenant, date, description, reference?, entries[]). Double entry. Dipanggil listener. |
| **ReportService** | Domains/Reporting/Services | getRingkasanOmzet(), getLaporanPenjualan(), getTopProduk(), getLaporanStok(), getLabaRugi(). Query efisien, pakai index. |

---

## 5. Middleware

| Middleware | Urutan | Tanggung jawab |
|------------|--------|----------------|
| **ResolveTenantFromAuth** | Setelah auth | Ambil `auth()->user()->tenant_id` → load Tenant → set context (helper `tenant()`). Jika user tidak punya tenant_id (super-admin), bisa set null atau skip route tenant. Abort/redirect jika user belum punya tenant. |
| **CheckSubscription** | Setelah ResolveTenantFromAuth | Cek SubscriptionService::canCreateSale() / canCreatePurchase() atau isActive(). Jika expired: redirect ke halaman subscription expired atau tampilkan banner; blok create sale & purchase. View data tetap boleh. |

**Penerapan:**  
- Route group untuk semua halaman tenant: auth → ResolveTenantFromAuth → CheckSubscription.  
- CheckSubscription bisa hanya untuk route yang mengubah data (create sale, create purchase) atau seluruh dashboard; view-only tetap diizinkan saat expired.

---

## 6. Event & Listener

| Event | Dipanggil dari | Listener | Aksi |
|-------|-----------------|----------|------|
| **SaleCompleted** | SaleService (setelah Sale + items + payment tersimpan) | 1) DeductSaleStock — panggil StockService::decreaseStock per item. 2) RecordSaleJournal — panggil JournalService (Debit Kas, Credit Penjualan). |
| **PurchaseCompleted** | PurchaseService (setelah Purchase + items tersimpan) | 1) AddPurchaseStock — panggil StockService::increaseStock per item. 2) RecordPurchaseJournal — panggil JournalService (Debit Persediaan, Credit Hutang). |

Listener ditempatkan di `Domains/Accounting/Listeners/` atau `app/Listeners/`; daftar di `EventServiceProvider` atau pakai attribute `#[ListensTo(SaleCompleted::class)]`.

---

## General Rules (Ringkas)

1. **Tidak ada logic subdomain/domain** untuk tenant; hanya `user->tenant_id`.
2. Semua tabel bisnis wajib **tenant_id** (indexed).
3. Business logic di **Service layer**; Livewire/Controller hanya koordinasi.
4. **Event + Listener** untuk perubahan stok dan jurnal.
5. Type hints, clean code, tidak hardcode.
6. Kerja **bertahap**; tidak lompat fase.
7. Arsitektur **mudah di-upgrade** ke multi-outlet (konsisten tenant_id, nanti bisa tambah outlet_id).

---

## Ringkasan Step Berikutnya

| Step | Isi |
|------|-----|
| **STEP 2** | tenants, tenant_settings, subscriptions, add tenant_id ke users, global scope BelongsToTenant, ResolveTenantFromAuth, CheckSubscription, helper tenant(). |
| **STEP 3** | products, Product model, ProductService, StockService (adjustment), Product CRUD Livewire. |
| **STEP 4** | sales, sale_items, payments, SaleService, SaleCompleted, listener (stock + journal), POS Livewire. |
| **STEP 5** | suppliers, purchases, purchase_items, PurchaseService, PurchaseCompleted, listener. |
| **STEP 6** | accounts, journals, journal_items, JournalService, double entry. |
| **STEP 7** | ReportService, laporan (omzet, penjualan, top produk, stok, laba rugi). |
| **STEP 8** | White-label (TenantSetting + CSS variables di layout). |
| **STEP 9** | Subscription logic (trial 7 hari, expired = block create, allow view). |

---

**Tidak ada implementasi di STEP 1.**  
Setelah plan ini dikonfirmasi, lanjut **STEP 2 — Foundation Setup**.
