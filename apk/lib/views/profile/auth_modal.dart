import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/custom_button.dart';

class AuthModal extends StatefulWidget {
  final String? notice;

  const AuthModal({super.key, this.notice});

  static Future<void> show(BuildContext context, {String? notice}) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => AuthModal(notice: notice),
    );
  }

  @override
  State<AuthModal> createState() => _AuthModalState();
}

class _AuthModalState extends State<AuthModal> {
  bool _isLoading = false;

  Future<void> _handleDemoLogin(String role) async {
    setState(() => _isLoading = true);
    try {
      await context.read<AuthProvider>().demoLogin(role);
      if (mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${role == "owner" ? "Dacha egasi" : "Mijoz"} sifatida tizimga kirdingiz!'),
            backgroundColor: AppColors.success,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Xatolik: $e'), backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.only(
        left: 24,
        right: 24,
        top: 24,
        bottom: MediaQuery.of(context).viewInsets.bottom + 32,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Drag handle
          Container(
            width: 44,
            height: 5,
            decoration: BoxDecoration(
              color: AppColors.border,
              borderRadius: BorderRadius.circular(10),
            ),
          ),
          const SizedBox(height: 20),

          const Text('🔐', style: TextStyle(fontSize: 44)),
          const SizedBox(height: 10),
          const Text(
            'Oromgo tizimiga kirish',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppColors.dark),
          ),
          const SizedBox(height: 6),
          Text(
            widget.notice ?? 'Dachalarni bron qilish, sharh qoldirish yoki e\'lon berish uchun profilingizga kiring.',
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 13.5, color: AppColors.textMuted),
          ),
          const SizedBox(height: 24),

          // Telegram Login Button
          CustomButton(
            text: 'Telegram orqali kirish',
            backgroundColor: AppColors.telegram,
            icon: const Icon(Icons.send_rounded, color: Colors.white, size: 20),
            onPressed: () {
              // Trigger Telegram login demo fallback
              _handleDemoLogin('user');
            },
          ),
          const SizedBox(height: 12),

          // Google Login Button
          CustomButton(
            text: 'Google orqali kirish',
            isOutlined: true,
            icon: const Icon(Icons.g_mobiledata, color: Colors.red, size: 26),
            onPressed: () {
              _handleDemoLogin('user');
            },
          ),

          const SizedBox(height: 20),
          const Row(
            children: [
              Expanded(child: Divider(color: AppColors.border)),
              Padding(
                padding: EdgeInsets.symmetric(horizontal: 10),
                child: Text('TEST UCHUN 1 BOSISHDA', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMuted)),
              ),
              Expanded(child: Divider(color: AppColors.border)),
            ],
          ),
          const SizedBox(height: 16),

          // Demo Owner Button
          CustomButton(
            text: '⚡ Dacha Egasi (Alisher) sifatida kirish',
            backgroundColor: AppColors.bgPage,
            textColor: AppColors.dark,
            isLoading: _isLoading,
            onPressed: () => _handleDemoLogin('owner'),
          ),
          const SizedBox(height: 10),

          // Demo Customer Button
          CustomButton(
            text: '👤 Mijoz (Jasur) sifatida kirish',
            backgroundColor: AppColors.bgPage,
            textColor: AppColors.dark,
            isLoading: _isLoading,
            onPressed: () => _handleDemoLogin('user'),
          ),
        ],
      ),
    );
  }
}
