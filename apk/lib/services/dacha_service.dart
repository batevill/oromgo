import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/amenity_model.dart';
import '../models/dacha_model.dart';
import '../models/review_model.dart';

class DachaService {
  final ApiClient _api = ApiClient();

  Future<List<DachaModel>> getDachas({
    String? region,
    int? capacity,
    String? currency,
    double? maxPrice,
    String? category,
  }) async {
    final Map<String, String> query = {};
    if (region != null && region.isNotEmpty) query['region'] = region;
    if (capacity != null) query['capacity'] = capacity.toString();
    if (currency != null) query['currency'] = currency;
    if (maxPrice != null) query['max_price'] = maxPrice.toString();
    if (category != null && category != 'all') query['category'] = category;

    final res = await _api.get(ApiConstants.dachas, queryParams: query);
    final List<dynamic> data = res['data'] ?? res;
    return data.map((json) => DachaModel.fromJson(json)).toList();
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
