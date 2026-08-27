class ApiConstants {
  // Wi-Fi Local Network URL for Phone & Laptop connection
  static const String baseUrl = 'http://172.16.6.102:10017/api';
  static const String localhostUrl = 'http://127.0.0.1:10017/api';

  // Dachas
  static const String dachas = '/dachas';
  static const String locations = '/locations';
  static String dachaDetail(int id) => '/dachas/$id';
  static String dachaCalendar(int id) => '/dachas/$id/calendar';
  static String calculatePrice(int id) => '/dachas/$id/calculate-price';
  static String dachaReviews(int id) => '/dachas/$id/reviews';
  static const String amenities = '/amenities';

  // Bookings & Favorites
  static String bookDacha(int id) => '/dachas/$id/book';
  static const String myBookings = '/my-bookings';
  static String cancelBooking(int id) => '/my-bookings/$id/cancel';
  static const String favorites = '/favorites';
  static String toggleFavorite(int dachaId) => '/favorites/$dachaId';

  // Notifications & Telegram
  static const String notifications = '/notifications';
  static String markNotificationRead(int id) => '/notifications/$id/read';
  static const String markAllNotificationsRead = '/notifications/read-all';
  static const String telegramBotLink = '/telegram/bot-link';

  // Owner Actions
  static const String ownerBookings = '/owner/bookings';
  static String ownerConfirmBooking(int id) => '/owner/bookings/$id/confirm';
  static String ownerRejectBooking(int id) => '/owner/bookings/$id/reject';
  static String ownerBlockDates(int dachaId) => '/owner/dachas/$dachaId/block-dates';
  static const String ownerDachas = '/owner/dachas';

  // Auth & User
  static const String userProfile = '/user';
  static const String demoLogin = '/demo-login';
}
