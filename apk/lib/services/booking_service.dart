import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/booking_model.dart';

class BookingService {
  final ApiClient _api = ApiClient();

  Future<BookingPriceCalculationModel> calculatePrice(
    int dachaId,
    String startDate,
    String endDate,
  ) async {
    final res = await _api.post(
      ApiConstants.calculatePrice(dachaId),
      body: {
        'start_date': startDate,
        'end_date': endDate,
      },
    );
    return BookingPriceCalculationModel.fromJson(res);
  }

  Future<Map<String, dynamic>> getCalendar(int dachaId) async {
    final res = await _api.get(ApiConstants.dachaCalendar(dachaId));
    return res is Map<String, dynamic> ? res : {};
  }

  Future<BookingModel> createBooking({
    required int dachaId,
    required String startDate,
    required String endDate,
    required int guestsCount,
    String? notes,
  }) async {
    final res = await _api.post(
      ApiConstants.bookDacha(dachaId),
      body: {
        'start_date': startDate,
        'end_date': endDate,
        'guests_count': guestsCount,
        'notes': notes,
      },
    );
    return BookingModel.fromJson(res['booking'] ?? res);
  }

  Future<List<BookingModel>> getMyBookings() async {
    final res = await _api.get(ApiConstants.myBookings);
    final List<dynamic> list = res['data'] ?? (res is List ? res : []);
    return list.map((json) => BookingModel.fromJson(json)).toList();
  }

  Future<void> cancelBooking(int id) async {
    await _api.post(ApiConstants.cancelBooking(id));
  }
}
