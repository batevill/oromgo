import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/booking_model.dart';
import '../models/dacha_model.dart';
import '../models/owner_report_model.dart';

class OwnerService {
  final ApiClient _api = ApiClient();

  Future<List<DachaModel>> getOwnerDachas() async {
    final res = await _api.get(ApiConstants.ownerDachas);
    final List<dynamic> list = res['data'] ?? (res is List ? res : []);
    return list.map((json) => DachaModel.fromJson(json)).toList();
  }

  Future<DachaModel> createDacha(Map<String, dynamic> body) async {
    final res = await _api.post(ApiConstants.ownerDachas, body: body);
    return DachaModel.fromJson(res['data'] ?? res);
  }

  Future<DachaModel> updateDacha(int id, Map<String, dynamic> body) async {
    final res = await _api.put('${ApiConstants.ownerDachas}/$id', body: body);
    return DachaModel.fromJson(res['data'] ?? res);
  }

  Future<void> deleteDacha(int id) async {
    await _api.delete('${ApiConstants.ownerDachas}/$id');
  }

  Future<List<BookingModel>> getOwnerBookings({String? status, String? source, int? dachaId}) async {
    final Map<String, dynamic> query = {};
    if (status != null && status.isNotEmpty) query['status'] = status;
    if (source != null && source.isNotEmpty) query['source'] = source;
    if (dachaId != null) query['dacha_id'] = dachaId.toString();

    final res = await _api.get(ApiConstants.ownerBookings, query: query);
    final List<dynamic> list = res['data'] ?? (res is List ? res : []);
    return list.map((json) => BookingModel.fromJson(json)).toList();
  }

  Future<void> confirmBooking(int bookingId) async {
    await _api.post(ApiConstants.ownerConfirmBooking(bookingId));
  }

  Future<void> rejectBooking(int bookingId) async {
    await _api.post(ApiConstants.ownerRejectBooking(bookingId));
  }

  Future<void> deleteBooking(int bookingId) async {
    await _api.delete(ApiConstants.ownerDeleteBooking(bookingId));
  }

  Future<OwnerReportModel> getOwnerReports({
    String period = 'this_month',
    int? dachaId,
    String? startDate,
    String? endDate,
  }) async {
    final Map<String, dynamic> query = {'period': period};
    if (dachaId != null) query['dacha_id'] = dachaId.toString();
    if (startDate != null) query['start_date'] = startDate;
    if (endDate != null) query['end_date'] = endDate;

    final res = await _api.get(ApiConstants.ownerReports, query: query);
    return OwnerReportModel.fromJson(res);
  }

  Future<BookingModel> createManualBooking({
    required int dachaId,
    required String startDate,
    required String endDate,
    required double totalPrice,
    String currency = 'USD',
    String source = 'telegram',
    String? customerName,
    String? customerPhone,
    int guestsCount = 1,
    String? notes,
  }) async {
    final res = await _api.post(
      ApiConstants.ownerManualBooking,
      body: {
        'dacha_id': dachaId,
        'start_date': startDate,
        'end_date': endDate,
        'total_price': totalPrice,
        'currency': currency,
        'source': source,
        'customer_name': customerName,
        'customer_phone': customerPhone,
        'guests_count': guestsCount,
        'notes': notes,
      },
    );
    return BookingModel.fromJson(res['booking'] ?? res);
  }

  Future<void> blockDates(
    int dachaId,
    String startDate,
    String endDate,
    String? reason, {
    double totalPrice = 0,
    String currency = 'USD',
    String source = 'manual',
    String? customerName,
    String? customerPhone,
  }) async {
    await _api.post(
      ApiConstants.ownerBlockDates(dachaId),
      body: {
        'start_date': startDate,
        'end_date': endDate,
        'reason': reason,
        'total_price': totalPrice,
        'currency': currency,
        'source': source,
        'customer_name': customerName,
        'customer_phone': customerPhone,
      },
    );
  }
}


