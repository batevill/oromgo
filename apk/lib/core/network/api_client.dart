import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;
  ApiClient._internal();

  String _baseUrl = ApiConstants.baseUrl;

  String get baseUrl => _baseUrl;

  static const String _storageKey = 'api_custom_base_url';

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final savedUrl = prefs.getString(_storageKey);
    if (savedUrl != null && savedUrl.trim().isNotEmpty) {
      _baseUrl = _normalizeUrl(savedUrl);
    }
  }

  Future<void> setBaseUrl(String url) async {
    _baseUrl = _normalizeUrl(url);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_storageKey, _baseUrl);
  }

  Future<void> resetBaseUrl() async {
    _baseUrl = ApiConstants.baseUrl;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_storageKey);
  }

  String _normalizeUrl(String url) {
    var trimmed = url.trim();
    if (!trimmed.startsWith('http://') && !trimmed.startsWith('https://')) {
      trimmed = 'http://$trimmed';
    }
    while (trimmed.endsWith('/')) {
      trimmed = trimmed.substring(0, trimmed.length - 1);
    }
    if (!trimmed.endsWith('/api')) {
      trimmed = '$trimmed/api';
    }
    return trimmed;
  }

  Future<bool> testConnection(String url) async {
    try {
      final normalized = _normalizeUrl(url);
      final uri = Uri.parse('$normalized/amenities');
      final response = await http.get(uri).timeout(const Duration(seconds: 5));
      return response.statusCode >= 200 && response.statusCode < 400;
    } catch (_) {
      return false;
    }
  }

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  Future<Map<String, String>> _getHeaders() async {
    final token = await getToken();
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  Future<dynamic> get(String endpoint, {Map<String, String>? queryParams}) async {
    try {
      var uri = Uri.parse('$_baseUrl$endpoint');
      if (queryParams != null && queryParams.isNotEmpty) {
        uri = uri.replace(queryParameters: queryParams);
      }

      final headers = await _getHeaders();
      final response = await http.get(uri, headers: headers).timeout(const Duration(seconds: 15));

      return _processResponse(response);
    } catch (e) {
      throw Exception('Tarmoq xatoligi: $e');
    }
  }

  Future<dynamic> post(String endpoint, {Map<String, dynamic>? body}) async {
    try {
      final uri = Uri.parse('$_baseUrl$endpoint');
      final headers = await _getHeaders();
      final response = await http
          .post(
            uri,
            headers: headers,
            body: body != null ? jsonEncode(body) : null,
          )
          .timeout(const Duration(seconds: 15));

      return _processResponse(response);
    } catch (e) {
      throw Exception('Tarmoq xatoligi: $e');
    }
  }

  Future<dynamic> delete(String endpoint) async {
    try {
      final uri = Uri.parse('$_baseUrl$endpoint');
      final headers = await _getHeaders();
      final response = await http.delete(uri, headers: headers).timeout(const Duration(seconds: 15));

      return _processResponse(response);
    } catch (e) {
      throw Exception('Tarmoq xatoligi: $e');
    }
  }

  dynamic _processResponse(http.Response response) {
    final body = response.body.isNotEmpty ? jsonDecode(response.body) : null;
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    } else {
      final message = body is Map && body['message'] != null
          ? body['message']
          : 'Xatolik yuz berdi (${response.statusCode})';
      throw Exception(message);
    }
  }
}
