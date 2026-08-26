import 'package:flutter/material.dart';
import '../models/amenity_model.dart';
import '../models/dacha_model.dart';
import '../services/dacha_service.dart';

class DachaProvider extends ChangeNotifier {
  final DachaService _service = DachaService();

  List<DachaModel> _dachas = [];
  List<AmenityModel> _amenities = [];
  DachaModel? _selectedDacha;
  bool _isLoading = false;
  String _selectedCategory = 'all';
  String? _selectedRegion;

  List<DachaModel> get dachas => _dachas;
  List<AmenityModel> get amenities => _amenities;
  DachaModel? get selectedDacha => _selectedDacha;
  bool get isLoading => _isLoading;
  String get selectedCategory => _selectedCategory;
  String? get selectedRegion => _selectedRegion;

  Future<void> fetchDachas({String? region, int? capacity, String? currency, double? maxPrice}) async {
    _isLoading = true;
    notifyListeners();
    try {
      _dachas = await _service.getDachas(
        region: region ?? _selectedRegion,
        capacity: capacity,
        currency: currency,
        maxPrice: maxPrice,
        category: _selectedCategory,
      );
    } catch (e) {
      debugPrint('Error fetching dachas: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchAmenities() async {
    try {
      _amenities = await _service.getAmenities();
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching amenities: $e');
    }
  }

  Future<void> fetchDachaDetail(int id) async {
    _isLoading = true;
    notifyListeners();
    try {
      _selectedDacha = await _service.getDachaDetail(id);
    } catch (e) {
      debugPrint('Error fetching detail: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void setCategory(String category) {
    _selectedCategory = category;
    fetchDachas();
  }

  void setRegion(String? region) {
    _selectedRegion = region;
    fetchDachas();
  }
}
