import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/dacha_model.dart';

class FavoriteService {
  final ApiClient _api = ApiClient();

  Future<List<DachaModel>> getFavorites() async {
    final res = await _api.get(ApiConstants.favorites);
    final List<dynamic> list = res['favorites'] ?? (res is List ? res : []);
    return list.map((json) => DachaModel.fromJson(json)).toList();
  }

  Future<bool> toggleFavorite(int dachaId) async {
    final res = await _api.post(ApiConstants.toggleFavorite(dachaId));
    return res['status'] == 'added';
  }
}
