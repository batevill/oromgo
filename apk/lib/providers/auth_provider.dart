import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';

class AuthProvider extends ChangeNotifier {
  final AuthService _authService = AuthService();

  UserModel? _user;
  bool _isLoading = false;

  UserModel? get user => _user;
  bool get isLoading => _isLoading;
  bool get isAuthenticated => _user != null;
  bool get isOwner => _user?.isOwner ?? false;

  Future<void> checkAuth() async {
    _isLoading = true;
    notifyListeners();
    _user = await _authService.getCurrentUser();
    _isLoading = false;
    notifyListeners();
  }

  Future<void> demoLogin(String role) async {
    _isLoading = true;
    notifyListeners();
    try {
      _user = await _authService.demoLogin(role: role);
    } catch (e) {
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    await _authService.logout();
    _user = null;
    notifyListeners();
  }
}
