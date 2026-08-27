import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/dacha_model.dart';

class FavoriteService {
  final ApiClient _api = ApiClient();

  Future<List<DachaModel>> getFavorites() async {
    final res = await _api.get(ApiConstants.favorites);
    dynamic raw = res;
    if (res is Map<String, dynamic>) {
      if (res['favorites'] != null) {
        if (res['favorites'] is Map && res['favorites']['data'] != null) {
          raw = res['favorites']['data'];
        } else if (res['favorites'] is List) {
          raw = res['favorites'];
        }
      }
    }
    final List<dynamic> list = raw is List ? raw : [];
    return list.map((json) => DachaModel.fromJson(json as Map<String, dynamic>)).toList();
  }

  Future<bool> toggleFavorite(int dachaId) async {
    final res = await _api.post(ApiConstants.toggleFavorite(dachaId));
    if (res is Map<String, dynamic>) {
      return res['is_favorite'] == true || res['status'] == 'added';
    }
    return false;
  }
}
