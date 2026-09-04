# OROMGO - Loyihaning Kelgusi Rivojlanish va Optimizatsiya Rejasi (TODO)

Ushbu hujjat Oromgo platformasini yuqori yuklama (High-Load), ko'p sonli foydalanuvchilar va keng miqyosda barqaror ishlashini ta'minlash bo'yicha belgilangan asosiy vazifalarni o'z ichiga oladi:

---

### [x] 1. 🗄️ Ma'lumotlar Bazasi va Indekslash (Database Indexing & Query Optimization) ✅
- [x] `bookings` jadvaliga `(dacha_id, status, start_date, end_date)` bo'yicha kompozit indekslar qo'shildi (Kalendar va sanalar to'qnashuvini tekshirish 10-50 baravargacha tezlashtirildi).
- [x] `dachas` jadvaliga `(status, region, district, weekday_price)` bo'yicha qidiruv indekslari o'rnatildi.
- [x] SQL so'rovlarida N+1 muammolari to'liq tekshirildi va `with()` / `withAvg()` / `withCount()` orqali optimallashtirildi.
- [x] PostgreSQL uchun ulanishlar puli va PgBouncer (Connection Pooling) sozlamalari kiritildi.

---

### [ ] 2. ⚡ Kesh Tizimi (Caching with Redis)
- [ ] Redis kesh drayverini ulash va sozlash (`CACHE_STORE=redis`).
- [ ] Kam o'zgaruvchi ma'lumotlarni keshga olish:
  - Viloyatlar, tumanlar va qulayliklar (`/api/locations`, `/api/amenities`) — 24 soatga.
  - Dachalar qidiruvi va ro'yxatini qisqa muddatli (5-10 daqiqa) keshga olish (`Cache::tags(['dachas'])`).
  - Dacha kalendaridagi band sanalarni 2-3 daqiqaga keshlab qo'yish.
- [ ] Yangi e'lon qo'shilganda yoki tahrirlanganda tegishli keshlarni avtomatik tozalash (Cache invalidation).

---

### [ ] 3. 📨 Asinxron Navbatlar (Background Queues & Jobs)
- [ ] `QUEUE_CONNECTION=redis` (yoki `database`) orqali asinxron navbat tizimini yo'lga qo'yish.
- [ ] Telegram bot orqali yuboriladigan xabarnomalar (`sendBookingRequestToOwner`, `sendBookingConfirmedToCustomer` va h.k.)ni `Job` orqali orqa fonga o'tkazish.
- [ ] SMS OTP kodlari va email xabarlarini fonga o'tkazish.
- [ ] Laravel Horizon orqali navbatlarni monitoring qilish.

---

### [x] 4. 🖼️ Media Fayllar va WebP Avtomatik Siqish (Media Optimization) ✅
- [x] Rasmlarni server diskida (`storage/public`) xavfsiz saqlash va kelgusida Cloudflare bepul CDN orqali tarqatishga tayyorlash.
- [x] Yuklanayotgan rasmlarni avtomatik tarzda zamonaviy `WebP` formatiga o'tkazish, EXIF orientatsiyasini to'g'rilash va sifatini yo'qotmasdan siqish (`ImageOptimizerService`).
- [x] Kartochkalar va tezkor ko'rinish uchun avtomatik `thumb_` (400px) o'lchamdagi WebP generatsiya qilish (`thumbnail_url`).
- [x] Tarmoq trafigi va rasmlar yuklanish vaqtini 80-90% ga qisqartirish.

---

### [ ] 5. 🔄 Real-time WebSockets (Laravel Reverb / Pusher — Polling o'rniga)
- [ ] Frontenddagi har 20 soniyalik muntazam so'rov (HTTP Polling) o'rniga **Laravel Reverb** (WebSockets)ni o'rnatish.
- [ ] Yangi bron tushganda yoki status o'zgarganda dacha egasi va mijozga bir zumda jonli push-xabar yetib borishini ta'minlash.
- [ ] Serverdagi ortiqcha bo'sh HTTP so'rovlarni 0 ga tushirish.

---

### [ ] 6. 🚀 Server va Production Optimizatsiyasi
- [ ] PHP-FPM va OPcache (`opcache.enable=1`, `opcache.jit=tracing`) sozlamalarini yoqish.
- [ ] Production build buyruqlarini doimiy avtomatlashtirish:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache
  ```
- [ ] **Laravel Octane (Swoole / RoadRunner):** Yuqori yuklamalarda PHP ni xotirada (in-memory) ushlab, javob berish tezligini 3-5 baravarga oshirish.
- [ ] Spam va botlardan himoyalanish uchun API endpointlariga `Rate Limiting` (Throttle) qo'yish.

---

### [ ] 7. 💳 To'lov Tizimlari Integratsiyasi (Click / Payme / Uzum)
- [ ] Click va Payme Merchant API lari bilan to'liq integratsiya.
- [ ] Dacha bron qilinganda avans (garov summasi) to'lash havolasini generatsiya qilish.
- [ ] To'lov muvaffaqiyatli o'tgach, bron statusini avtomatik `confirmed` ga o'tkazish (Webhook).
