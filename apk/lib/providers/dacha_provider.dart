import 'package:flutter/material.dart';
import '../models/amenity_model.dart';
import '../models/dacha_model.dart';
import '../services/dacha_service.dart';

class DachaProvider extends ChangeNotifier {
  final DachaService _service = DachaService();

  List<DachaModel> _dachas = [];
  List<AmenityModel> _amenities = [];
  Map<String, Map<String, List<String>>> _locationsHierarchy = {};
  DachaModel? _selectedDacha;
  bool _isLoading = false;
  String? _errorMessage;

  String _selectedCategory = 'all';
  String? _selectedRegion;
  String? _selectedDistrict;
  String? _selectedMahalla;
  List<int> _selectedAmenityIds = [];
  String? _searchQuery;

  List<DachaModel> get dachas => _dachas;
  List<AmenityModel> get amenities => _amenities;
  Map<String, Map<String, List<String>>> get locationsHierarchy => _locationsHierarchy;
  DachaModel? get selectedDacha => _selectedDacha;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  String get selectedCategory => _selectedCategory;
  String? get selectedRegion => _selectedRegion;
  String? get selectedDistrict => _selectedDistrict;
  String? get selectedMahalla => _selectedMahalla;
  List<int> get selectedAmenityIds => _selectedAmenityIds;
  String? get searchQuery => _searchQuery;

  bool get hasActiveLocationFilter =>
      _selectedRegion != null ||
      _selectedDistrict != null ||
      _selectedMahalla != null ||
      _selectedAmenityIds.isNotEmpty ||
      (_searchQuery != null && _searchQuery!.isNotEmpty);

  String get activeLocationLabel {
    final parts = <String>[];
    if (_selectedRegion != null) parts.add(_selectedRegion!);
    if (_selectedDistrict != null) parts.add(_selectedDistrict!);
    if (_selectedMahalla != null) parts.add(_selectedMahalla!);
    if (_selectedAmenityIds.isNotEmpty) parts.add('${_selectedAmenityIds.length} ta qulaylik');
    return parts.isEmpty ? 'Barcha hududlar' : parts.join(' > ');
  }

  Future<void> fetchDachas({
    String? region,
    String? district,
    String? mahalla,
    String? q,
    int? capacity,
    String? currency,
    double? maxPrice,
    List<int>? amenityIds,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _dachas = await _service.getDachas(
        region: region ?? _selectedRegion,
        district: district ?? _selectedDistrict,
        mahalla: mahalla ?? _selectedMahalla,
        q: q ?? _searchQuery,
        capacity: capacity,
        currency: currency,
        maxPrice: maxPrice,
        category: _selectedCategory,
        amenityIds: amenityIds ?? _selectedAmenityIds,
      );
      _errorMessage = null;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception:', '').trim();
      debugPrint('Error fetching dachas: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchLocations() async {
    try {
      _locationsHierarchy = await _service.getLocations();
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching locations: $e');
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
    _errorMessage = null;
    notifyListeners();

    try {
      _selectedDacha = await _service.getDachaDetail(id);
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception:', '').trim();
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

  void setFilter({
    String? region,
    String? district,
    String? mahalla,
    List<int>? amenityIds,
  }) {
    _selectedRegion = region;
    _selectedDistrict = district;
    _selectedMahalla = mahalla;
    if (amenityIds != null) {
      _selectedAmenityIds = List.from(amenityIds);
    }
    fetchDachas();
  }

  void setLocationFilter({String? region, String? district, String? mahalla}) {
    _selectedRegion = region;
    _selectedDistrict = district;
    _selectedMahalla = mahalla;
    fetchDachas();
  }

  void toggleAmenity(int amenityId) {
    if (_selectedAmenityIds.contains(amenityId)) {
      _selectedAmenityIds.remove(amenityId);
    } else {
      _selectedAmenityIds.add(amenityId);
    }
    fetchDachas();
  }

  void setSearchQuery(String? query) {
    _searchQuery = query;
    fetchDachas();
  }

  void clearFilters() {
    _selectedRegion = null;
    _selectedDistrict = null;
    _selectedMahalla = null;
    _selectedAmenityIds.clear();
    _searchQuery = null;
    _selectedCategory = 'all';
    fetchDachas();
  }
}
