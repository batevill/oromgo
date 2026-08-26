import 'package:flutter/material.dart';
import '../models/notification_model.dart';
import '../services/notification_service.dart';
import '../services/owner_service.dart';

class NotificationProvider extends ChangeNotifier {
  final NotificationService _notificationService = NotificationService();
  final OwnerService _ownerService = OwnerService();

  List<NotificationModel> _notifications = [];
  int _unreadCount = 0;
  bool _hasTelegramLinked = false;
  bool _isLoading = false;

  List<NotificationModel> get notifications => _notifications;
  int get unreadCount => _unreadCount;
  bool get hasTelegramLinked => _hasTelegramLinked;
  bool get isLoading => _isLoading;

  Future<void> fetchNotifications() async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await _notificationService.getNotifications();
      _notifications = res['notifications'] as List<NotificationModel>;
      _unreadCount = res['unread_count'] as int;
      _hasTelegramLinked = res['has_telegram_linked'] as bool;
    } catch (e) {
      debugPrint('Error fetching notifications: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> markAsRead(int id) async {
    final notif = _notifications.firstWhere((n) => n.id == id, orElse: () => _notifications.first);
    if (notif.isRead) return;

    notif.isRead = true;
    _unreadCount = (_unreadCount - 1).clamp(0, 999);
    notifyListeners();

    try {
      await _notificationService.markAsRead(id);
    } catch (_) {}
  }

  Future<void> markAllAsRead() async {
    for (var n in _notifications) {
      n.isRead = true;
    }
    _unreadCount = 0;
    notifyListeners();

    try {
      await _notificationService.markAllAsRead();
    } catch (_) {}
  }

  Future<void> handleOwnerDecision(int bookingId, String action, int notifId) async {
    try {
      if (action == 'confirm') {
        await _ownerService.confirmBooking(bookingId);
      } else {
        await _ownerService.rejectBooking(bookingId);
      }
      await markAsRead(notifId);
      await fetchNotifications();
    } catch (e) {
      rethrow;
    }
  }

  Future<String?> getTelegramBotLink() async {
    return await _notificationService.getTelegramBotLink();
  }
}
