import 'package:flutter/material.dart';
import '../models/booking_model.dart';
import '../services/booking_service.dart';

class BookingProvider extends ChangeNotifier {
  final BookingService _service = BookingService();

  List<BookingModel> _myBookings = [];
  BookingPriceCalculationModel? _calculation;
  bool _isLoading = false;
  bool _isCalculating = false;

  List<BookingModel> get myBookings => _myBookings;
  BookingPriceCalculationModel? get calculation => _calculation;
  bool get isLoading => _isLoading;
  bool get isCalculating => _isCalculating;

  Future<void> fetchMyBookings() async {
    _isLoading = true;
    notifyListeners();
    try {
      _myBookings = await _service.getMyBookings();
    } catch (e) {
      debugPrint('Error fetching my bookings: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> calculatePrice(int dachaId, String startDate, String endDate) async {
    _isCalculating = true;
    notifyListeners();
    try {
      _calculation = await _service.calculatePrice(dachaId, startDate, endDate);
    } catch (e) {
      debugPrint('Error calculating price: $e');
      _calculation = null;
    } finally {
      _isCalculating = false;
      notifyListeners();
    }
  }

  Future<BookingModel> bookDacha({
    required int dachaId,
    required String startDate,
    required String endDate,
    required int guestsCount,
    String? notes,
  }) async {
    _isLoading = true;
    notifyListeners();
    try {
      final res = await _service.createBooking(
        dachaId: dachaId,
        startDate: startDate,
        endDate: endDate,
        guestsCount: guestsCount,
        notes: notes,
      );
      await fetchMyBookings();
      return res;
    } catch (e) {
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> cancelBooking(int id) async {
    _isLoading = true;
    notifyListeners();
    try {
      await _service.cancelBooking(id);
      await fetchMyBookings();
    } catch (e) {
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
