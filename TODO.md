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

### [ ] 4. 🖼️ Media Fayllar va CDN (Cloudflare / S3 & WebP Avtomatik Siqish)
- [ ] Dacha rasmlari va videolarini to'g'ridan-to'g'ri server diskida emas, AWS S3 / DigitalOcean Spaces yoki Cloudflare CDN orqali tarqatish.
- [ ] Yuklanayotgan rasmlarni avtomatik tarzda zamonaviy `WebP` formatiga o'tkazish va siqish (Intervention Image yordamida).
- [ ] Har bir rasm uchun turli o'lchamlar generatsiya qilish (thumbnail: 400px, medium: 800px, full: 1600px).
- [ ] Tarmoq trafigi va yuklanish vaqtini 70% ga qisqartirish.

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
