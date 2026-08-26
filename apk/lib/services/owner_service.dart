import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/booking_model.dart';

class OwnerService {
  final ApiClient _api = ApiClient();

  Future<List<BookingModel>> getOwnerBookings() async {
    final res = await _api.get(ApiConstants.ownerBookings);
    final List<dynamic> list = res['data'] ?? (res is List ? res : []);
    return list.map((json) => BookingModel.fromJson(json)).toList();
  }

  Future<void> confirmBooking(int bookingId) async {
    await _api.post(ApiConstants.ownerConfirmBooking(bookingId));
  }

  Future<void> rejectBooking(int bookingId) async {
    await _api.post(ApiConstants.ownerRejectBooking(bookingId));
  }

  Future<void> blockDates(int dachaId, String startDate, String endDate, String? reason) async {
    await _api.post(
      ApiConstants.ownerBlockDates(dachaId),
      body: {
        'start_date': startDate,
        'end_date': endDate,
        'reason': reason,
      },
    );
  }
}
