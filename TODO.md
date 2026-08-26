# OROMGO - Loyihaning Kelgusi Rivojlanish Rejasi (TODO)

Ushbu ro'yxat Oromgo platformasida navbatda turgan va amalga oshirilishi kerak bo'lgan asosiy vazifalarni o'z ichiga oladi:

---

### [x] 1. 💬 Telegram Bot orqali Jonli Xabarnomalar (Notifications) & Sayt Bildirishnomalar Markazi
- [x] Dacha egasiga yangi bron tushganda Telegram bot orqali darhol xabar va `[Tasdiqlash]` / `[Rad etish]` tugmalarini yuborish.
- [x] Bron tasdiqlanganda yoki bekor qilinganda mijozga Telegram orqali xabar yuborish.
- [x] Eslatma xabarlari (Bron sanasidan 1 kun oldin mijoz va dacha egasiga eslatish).
- [x] Saytda to'liq Bildirishnomalar markazi (Notification Center), o'qilmaganlar hisoblagichi (badge) va dacha egasi uchun to'g'ridan-to'g'ri saytdan tasdiqlash/rad etish tugmalari.
- [x] Telegram bot bilan sayt hisobini 1 bosishda ulash (Deep link: `/start bind_{userId}`).

---

### [ ] 2. 💳 To'lov tizimlari (Click / Payme / Uzum)
- [ ] Click va Payme Merchant API lari bilan integratsiya.
- [ ] Dacha bron qilinganda avans (garov summasi) to'lash havolasini generatsiya qilish.
- [ ] To'lov muvaffaqiyatli o'tgach, bron statusini avtomatik `confirmed` ga o'tkazish (Webhook).

---

### [ ] 3. 📱 Mobil Ilova (Mobile App — Flutter / React Native)
- [ ] `oromgo/mobile` katalogida mobil ilova loyihasini yaratish.
- [ ] Barcha mavjud Backend API larni mobil ilovaga ulash (Auth, Dachalar, Qidiruv, Kalendar, Bron qilish).
- [ ] iOS va Android uchun chiroyli mobil interfeysni qurish.
