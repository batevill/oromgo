import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/notification_model.dart';

class NotificationService {
  final ApiClient _api = ApiClient();

  Future<Map<String, dynamic>> getNotifications() async {
    final res = await _api.get(ApiConstants.notifications);
    final List<dynamic> list = res['notifications']?['data'] ?? [];
    final notifications = list.map((json) => NotificationModel.fromJson(json)).toList();
    final unreadCount = res['unread_count'] ?? 0;
    final hasTelegramLinked = res['has_telegram_linked'] == true;

    return {
      'notifications': notifications,
      'unread_count': unreadCount,
      'has_telegram_linked': hasTelegramLinked,
    };
  }

  Future<void> markAsRead(int id) async {
    await _api.post(ApiConstants.markNotificationRead(id));
  }

  Future<void> markAllAsRead() async {
    await _api.post(ApiConstants.markAllNotificationsRead);
  }

  Future<String?> getTelegramBotLink() async {
    final res = await _api.get(ApiConstants.telegramBotLink);
    return res?['link'];
  }
}
