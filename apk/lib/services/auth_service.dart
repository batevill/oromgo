import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../models/user_model.dart';

class AuthService {
  final ApiClient _api = ApiClient();

  Future<UserModel?> getCurrentUser() async {
    try {
      final token = await _api.getToken();
      if (token == null) return null;

      final res = await _api.get(ApiConstants.userProfile);
      if (res != null) {
        return UserModel.fromJson(res);
      }
    } catch (_) {}
    return null;
  }

  Future<UserModel> demoLogin({String role = 'owner'}) async {
    final res = await _api.post(ApiConstants.demoLogin, body: {'role': role});
    final token = res['token'];
    final userJson = res['user'];

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    await prefs.setString('user_data', jsonEncode(userJson));

    return UserModel.fromJson(userJson);
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_data');
  }
}
