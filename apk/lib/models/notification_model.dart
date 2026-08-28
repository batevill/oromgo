import 'booking_model.dart';

class NotificationModel {
  final int id;
  final int userId;
  final int? bookingId;
  final String type; // booking_created, booking_confirmed, booking_cancelled, booking_reminder, info
  final String title;
  final String message;
  final Map<String, dynamic>? data;
  bool isRead;
  final String? createdAt;
  final BookingModel? booking;

  NotificationModel({
    required this.id,
    required this.userId,
    this.bookingId,
    required this.type,
    required this.title,
    required this.message,
    this.data,
    this.isRead = false,
    this.createdAt,
    this.booking,
  });

  bool get isBookingCreated => type == 'booking_created';
  bool get isBookingConfirmed => type == 'booking_confirmed';
  bool get isBookingCancelled => type == 'booking_cancelled';
  bool get isReminder => type == 'booking_reminder';

  String? get dachaName => data?['dacha_name'];
  int? get dachaId {
    if (data != null && data!['dacha_id'] != null) {
      return int.tryParse(data!['dacha_id'].toString());
    }
    if (booking != null && booking!.dacha != null) {
      return booking!.dacha!.id;
    }
    return null;
  }
  String? get guestName => data?['guest_name'];
  String? get guestPhone => data?['guest_phone'];
  String? get startDate => data?['start_date'];
  String? get endDate => data?['end_date'];
  dynamic get totalPrice => data?['total_price'];
  String? get currency => data?['currency'] ?? 'USD';
  String? get status => data?['status'];
  String? get notes => data?['notes'];

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      userId: int.tryParse(json['user_id']?.toString() ?? '0') ?? 0,
      bookingId: json['booking_id'] != null ? int.tryParse(json['booking_id'].toString()) : null,
      type: json['type'] ?? 'info',
      title: json['title'] ?? '',
      message: json['message'] ?? '',
      data: json['data'] is Map ? Map<String, dynamic>.from(json['data']) : null,
      isRead: json['is_read'] == true || json['is_read'] == 1,
      createdAt: json['created_at'],
      booking: json['booking'] != null ? BookingModel.fromJson(json['booking']) : null,
    );
  }
}
