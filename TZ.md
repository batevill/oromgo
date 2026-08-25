# OROMGO Loyihasi - Texnik Topshiriq (TZ)

## 1. Umumiy Ma'lumot
**Loyiha nomi:** Oromgo
**Loyiha turi:** Dachalarni ijaraga berish va qidirib topish uchun mo'ljallangan platforma (Web va Mobil Ilova).
**Asosiy g'oya:** Odamlarga dam olish uchun dachalarni oson izlab topish, bron qilish (band qilish) hamda dacha egalariga o'z mulklarini ijaraga berishlari uchun qulay muhit yaratish.

## 2. Loyihaning Maqsadi
Foydalanuvchilarga O'zbekiston bo'ylab (yoki ma'lum hududlarda) joylashgan dachalarni tez va oson topish, ularning holati, rasmlari, videolari, joylashuvi va narxlari bilan tanishish imkoniyatini berish. Shuningdek, qaysi kunlari band yoki bo'sh ekanligini ko'rish orqali jarayonni shaffof va oson qilish.

## 3. Asosiy Foydalanuvchi Rollari
Loyiha doirasida asosan quyidagi 3 ta rol mavjud bo'ladi:
1. **Dacha egasi (E'lon beruvchi):** O'z dacha(lar)ini platformaga qo'shadigan, ularning ma'lumotlarini (narx, rasm, kalendar) boshqaradigan shaxs.
2. **Mijoz (Foydalanuvchi):** Dacha qidirayotgan, ularning ma'lumotlarini ko'ruvchi va kerakli kunlar uchun bron qiluvchi shaxs.
3. **Administrator:** Tizimdagi e'lonlarni tekshiruvchi (moderatsiya), foydalanuvchilar va to'lovlarni nazorat qiluvchi boshqaruvchi.

## 4. Funksional Talablar

### 4.1. Dacha Egasi uchun (E'lon beruvchi)
- **Ro'yxatdan o'tish va Profil:** Telefon raqam (OTP) orqali ro'yxatdan o'tish, shaxsiy ma'lumotlarni kiritish.
- **E'lon qo'shish (Dacha yaratish):**
  - **Asosiy ma'lumotlar:** Dacha nomi, sig'imi (necha kishilik), xonalar soni, qo'shimcha qulayliklar (hovuz, sauna, bilyard, WiFi va h.k.).
  - **Media fayllar:** Bir nechta rasm va video yuklash imkoniyati.
  - **Lokatsiya (Manzil):** Viloyat, tuman, mahalla, uy raqami kiritish va xarita orqali aniq lokatsiyani belgilash (Google Maps yoki Yandex Maps).
  - **Narxlash:** Odatiy kunlar, dam olish kunlari (shanba-yakshanba) va bayram kunlari uchun alohida narxlar belgilash.
- **Bandlikni boshqarish (Kalendar):** Mijozlar tomonidan band qilingan kunlarni tasdiqlash yoki dacha o'zi tomonidan boshqa yo'l bilan ijaraga berilganda kalendarda shu kunlarni "band" deb belgilab qo'yish.
- **Statistika va xabarnomalar:** E'lonining ko'rilishlar soni, yangi bronlar haqida push-xabarnomalar (SMS, Email yoki App ichida) olish.

### 4.2. Mijoz (Foydalanuvchi) uchun
- **Qidiruv va Filtrlar:**
  - Viloyat va tuman bo'yicha qidirish.
  - Xarita orqali qidirish.
  - Bo'sh sanalar bo'yicha qidirish (Kirish va chiqish sanasini belgilab).
  - Narxi bo'yicha oralig'ni belgilash.
  - Qulayliklariga ko'ra filtrlar (basseyn, tog' yonbag'ri, WiFi va boshqalar).
- **E'lonni ko'rish:**
  - Dacha rasmlari va videosini ko'rish.
  - Egasi haqidagi ochiq ma'lumotlar (ism, reyting).
  - Aniq manzil va xaritada ko'rish.
  - Kalendar orqali qaysi kunlar bo'sh, qaysi kunlar band ekanligini ko'rish.
- **Bron qilish (Band qilish):** Tanlangan sanalar uchun dachani bron qilish va egasiga so'rov yuborish.
- **Saqlanganlar:** O'ziga yoqqan dachalarni "Sevimlilar" safiga qo'shish.
- **Fikr va Reyting:** Dacha bo'yicha dam olib qaytganidan so'ng izoh (review) qoldirish va baholash.

### 4.3. Administrator uchun (Admin Panel)
- Foydalanuvchilarni va Dacha egalarini boshqarish.
- Yangi qo'shilgan e'lonlarni tekshirish (Moderatsiya - tasdiqlash yoki rad etish).
- Umumiy statistika (tashriflar, tranzaksiyalar, qo'shilgan dachalar soni) ko'rish.

## 5. Texnik Talablar va Stack
1. **Backend:** Laravel 11.x, PHP 8.x
2. **Ma'lumotlar bazasi:** PostgreSQL 16+
3. **Web Frontend:** Blade / Vue.js / React (Tanlovga ko'ra, hozircha Web qismi boshlandi)
4. **Mobil Ilova:** Flutter yoki React Native (Kelajakda iOS va Android uchun)
5. **Fayl Saqlash:** AWS S3 yoki DigitalOcean Spaces (rasm va videolar uchun)
6. **Xarita API:** Yandex Maps API yoki Google Maps API
7. **SMS Gateway:** Eskiz.uz yoki Playmobile (OTP tasdiqlash uchun)

## 6. Ma'lumotlar Bazasining Taxminiy Arxitekturasi (Skelet)
- `users`: ID, ism, telefon, rol (admin, owner, user), parol.
- `dachas`: ID, owner_id, viloyat, tuman, mahalla, lat, lng, narx, holat, tavsif.
- `dacha_media`: ID, dacha_id, fayl_turi (rasm/video), URL.
- `dacha_amenities`: ID, dacha_id, qulaylik_id (bog'lanish jadvali).
- `bookings`: ID, dacha_id, user_id, boshlanish_sanasi, tugash_sanasi, jami_narx, status (kutilmoqda, tasdiqlangan, bekor qilingan).

## 7. Kelgusi bosqichlar (Milestones)
- **1-bosqich:** Ma'lumotlar bazasi strukturasini tuzish va Laravel loyihasini sozlash.
- **2-bosqich:** Dacha egasi va Mijoz uchun API'larni ishlab chiqish (Auth, Dacha CRUD, Media upload).
- **3-bosqich:** Filtrlash, Qidiruv tizimi va Kalendar (Band qilish) mantiqlarini yaratish.
- **4-bosqich:** Web qismining (UI) ulanishi (agar Admin panel va Web sayt bo'lsa).
- **5-bosqich:** Mobil ilovani (API orqali) ulab integratsiya qilish.
