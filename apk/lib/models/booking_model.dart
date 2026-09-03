import 'dacha_model.dart';
import 'user_model.dart';

class BookingPriceCalculationModel {
  final int totalDays;
  final int weekdaysCount;
  final int weekendsCount;
  final double weekdaysTotal;
  final double weekendsTotal;
  final double totalPrice;
  final String currency;

  BookingPriceCalculationModel({
    required this.totalDays,
    required this.weekdaysCount,
    required this.weekendsCount,
    required this.weekdaysTotal,
    required this.weekendsTotal,
    required this.totalPrice,
    required this.currency,
  });

  factory BookingPriceCalculationModel.fromJson(Map<String, dynamic> json) {
    return BookingPriceCalculationModel(
      totalDays: int.tryParse(json['total_days']?.toString() ?? '1') ?? 1,
      weekdaysCount: int.tryParse(json['weekdays_count']?.toString() ?? '0') ?? 0,
      weekendsCount: int.tryParse(json['weekends_count']?.toString() ?? '0') ?? 0,
      weekdaysTotal: double.tryParse(json['weekdays_total']?.toString() ?? '0') ?? 0.0,
      weekendsTotal: double.tryParse(json['weekends_total']?.toString() ?? '0') ?? 0.0,
      totalPrice: double.tryParse(json['total_price']?.toString() ?? '0') ?? 0.0,
      currency: json['currency'] ?? 'USD',
    );
  }
}

class BookingModel {
  final int id;
  final int dachaId;
  final int userId;
  final String startDate;
  final String endDate;
  final double totalPrice;
  final String currency;
  final int guestsCount;
  final String? notes;
  final String status; // pending, confirmed, cancelled, completed
  final String source; // app, telegram, phone, manual
  final String? customerName;
  final String? customerPhone;
  final String? createdAt;
  final DachaModel? dacha;
  final UserModel? user;

  BookingModel({
    required this.id,
    required this.dachaId,
    required this.userId,
    required this.startDate,
    required this.endDate,
    required this.totalPrice,
    this.currency = 'USD',
    required this.guestsCount,
    this.notes,
    required this.status,
    this.source = 'app',
    this.customerName,
    this.customerPhone,
    this.createdAt,
    this.dacha,
    this.user,
  });

  bool get isPending => status == 'pending';
  bool get isConfirmed => status == 'confirmed';
  bool get isCancelled => status == 'cancelled';

  String get clientName {
    if (user != null && user!.name.isNotEmpty) return user!.name;
    if (customerName != null && customerName!.isNotEmpty) return customerName!;
    return 'Noma\'lum mijoz';
  }

  String get clientPhone {
    if (user != null && user!.phone.isNotEmpty) return user!.phone;
    if (customerPhone != null && customerPhone!.isNotEmpty) return customerPhone!;
    return '-';
  }

  String get sourceLabel {
    switch (source) {
      case 'telegram':
        return 'Telegram 📱';
      case 'phone':
        return 'Telefon 📞';
      case 'manual':
        return 'Qo\'lda 📝';
      default:
        return 'Oromgo 🌟';
    }
  }

  factory BookingModel.fromJson(Map<String, dynamic> json) {
    return BookingModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      dachaId: int.tryParse(json['dacha_id']?.toString() ?? '0') ?? 0,
      userId: int.tryParse(json['user_id']?.toString() ?? '0') ?? 0,
      startDate: json['start_date']?.toString().split('T')[0] ?? '',
      endDate: json['end_date']?.toString().split('T')[0] ?? '',
      totalPrice: double.tryParse(json['total_price']?.toString() ?? '0') ?? 0.0,
      currency: json['currency'] ?? 'USD',
      guestsCount: int.tryParse(json['guests_count']?.toString() ?? '1') ?? 1,
      notes: json['notes'],
      status: json['status'] ?? 'pending',
      source: json['source'] ?? 'app',
      customerName: json['customer_name'],
      customerPhone: json['customer_phone'],
      createdAt: json['created_at'],
      dacha: json['dacha'] != null ? DachaModel.fromJson(json['dacha']) : null,
      user: json['user'] != null ? UserModel.fromJson(json['user']) : null,
    );
  }
}
