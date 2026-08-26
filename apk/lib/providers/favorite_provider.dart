import 'package:flutter/material.dart';
import '../models/dacha_model.dart';
import '../services/favorite_service.dart';

class FavoriteProvider extends ChangeNotifier {
  final FavoriteService _service = FavoriteService();

  List<DachaModel> _favorites = [];
  Set<int> _favoriteIds = {};
  bool _isLoading = false;

  List<DachaModel> get favorites => _favorites;
  Set<int> get favoriteIds => _favoriteIds;
  bool get isLoading => _isLoading;

  bool isFavorite(int dachaId) => _favoriteIds.contains(dachaId);

  Future<void> fetchFavorites() async {
    _isLoading = true;
    notifyListeners();
    try {
      _favorites = await _service.getFavorites();
      _favoriteIds = _favorites.map((d) => d.id).toSet();
    } catch (e) {
      debugPrint('Error fetching favorites: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> toggleFavorite(DachaModel dacha) async {
    final exists = _favoriteIds.contains(dacha.id);
    if (exists) {
      _favoriteIds.remove(dacha.id);
      _favorites.removeWhere((d) => d.id == dacha.id);
    } else {
      _favoriteIds.add(dacha.id);
      _favorites.add(dacha);
    }
    notifyListeners();

    try {
      await _service.toggleFavorite(dacha.id);
    } catch (e) {
      // Revert if failed
      if (exists) {
        _favoriteIds.add(dacha.id);
        _favorites.add(dacha);
      } else {
        _favoriteIds.remove(dacha.id);
        _favorites.removeWhere((d) => d.id == dacha.id);
      }
      notifyListeners();
    }
  }
}
