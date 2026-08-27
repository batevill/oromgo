import 'package:flutter/material.dart';
import '../models/dacha_model.dart';
import '../services/favorite_service.dart';

class FavoriteProvider extends ChangeNotifier {
  final FavoriteService _service = FavoriteService();

  List<DachaModel> _favorites = [];
  Set<int> _favoriteIds = {};
  final Set<int> _pendingIds = {};
  bool _isLoading = false;

  List<DachaModel> get favorites => _favorites;
  Set<int> get favoriteIds => _favoriteIds;
  bool get isLoading => _isLoading;

  bool isFavorite(int dachaId) => _favoriteIds.contains(dachaId);
  bool isPending(int dachaId) => _pendingIds.contains(dachaId);

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

  Future<bool> toggleFavorite(DachaModel dacha) async {
    if (_pendingIds.contains(dacha.id)) return _favoriteIds.contains(dacha.id);

    _pendingIds.add(dacha.id);
    final exists = _favoriteIds.contains(dacha.id);

    // 1. Optimistic Instant UI Update
    if (exists) {
      _favoriteIds.remove(dacha.id);
      _favorites.removeWhere((d) => d.id == dacha.id);
    } else {
      _favoriteIds.add(dacha.id);
      if (!_favorites.any((d) => d.id == dacha.id)) {
        _favorites.add(dacha);
      }
    }
    notifyListeners();

    // 2. Background Sync
    try {
      await _service.toggleFavorite(dacha.id);
      return !exists;
    } catch (e) {
      // Revert on error
      if (exists) {
        _favoriteIds.add(dacha.id);
        if (!_favorites.any((d) => d.id == dacha.id)) {
          _favorites.add(dacha);
        }
      } else {
        _favoriteIds.remove(dacha.id);
        _favorites.removeWhere((d) => d.id == dacha.id);
      }
      notifyListeners();
      return exists;
    } finally {
      _pendingIds.remove(dacha.id);
      notifyListeners();
    }
  }
}
