import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/amenity_model.dart';
import '../models/dacha_model.dart';
import '../models/review_model.dart';

class DachaService {
  final ApiClient _api = ApiClient();

  Future<List<DachaModel>> getDachas({
    String? region,
    String? district,
    String? mahalla,
    String? q,
    int? capacity,
    String? currency,
    double? maxPrice,
    String? category,
    List<int>? amenityIds,
  }) async {
    final Map<String, String> query = {};
    if (region != null && region.isNotEmpty) query['region'] = region;
    if (district != null && district.isNotEmpty) query['district'] = district;
    if (mahalla != null && mahalla.isNotEmpty) query['mahalla'] = mahalla;
    if (q != null && q.isNotEmpty) query['q'] = q;
    if (capacity != null) query['capacity'] = capacity.toString();
    if (currency != null) query['currency'] = currency;
    if (maxPrice != null) query['max_price'] = maxPrice.toString();
    if (category != null && category != 'all') query['category'] = category;
    if (amenityIds != null && amenityIds.isNotEmpty) query['amenities'] = amenityIds.join(',');

    final res = await _api.get(ApiConstants.dachas, queryParams: query);
    final List<dynamic> data = res is Map && res.containsKey('data') ? res['data'] : (res is List ? res : []);
    return data.map((json) => DachaModel.fromJson(json)).toList();
  }

  Future<Map<String, Map<String, List<String>>>> getLocations() async {
    try {
      final res = await _api.get(ApiConstants.locations);
      final Map<String, Map<String, List<String>>> result = {};
      if (res is Map) {
        res.forEach((regKey, regVal) {
          if (regVal is Map) {
            final Map<String, List<String>> districtMap = {};
            regVal.forEach((distKey, distVal) {
              if (distVal is List) {
                districtMap[distKey.toString()] = distVal.map((e) => e.toString()).toList();
              }
            });
            result[regKey.toString()] = districtMap;
          }
        });
      }
      return result;
    } catch (_) {
      return {};
    }
  }

  Future<DachaModel> getDachaDetail(int id) async {
    final res = await _api.get(ApiConstants.dachaDetail(id));
    final json = res['dacha'] ?? res;
    return DachaModel.fromJson(json);
  }

  Future<List<AmenityModel>> getAmenities() async {
    final res = await _api.get(ApiConstants.amenities);
    final List<dynamic> list = res is List ? res : (res['data'] ?? []);
    return list.map((json) => AmenityModel.fromJson(json)).toList();
  }

  Future<List<ReviewModel>> getDachaReviews(int dachaId) async {
    final res = await _api.get(ApiConstants.dachaReviews(dachaId));
    final List<dynamic> list = res is List ? res : (res['data'] ?? []);
    return list.map((json) => ReviewModel.fromJson(json)).toList();
  }

  Future<ReviewModel> submitReview(int dachaId, int rating, String comment) async {
    final res = await _api.post(
      ApiConstants.dachaReviews(dachaId),
      body: {
        'rating': rating,
        'comment': comment,
      },
    );
    return ReviewModel.fromJson(res['review'] ?? res);
  }
}
