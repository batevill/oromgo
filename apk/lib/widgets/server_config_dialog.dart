import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../core/constants/api_constants.dart';
import '../core/constants/app_colors.dart';
import '../core/network/api_client.dart';
import '../providers/auth_provider.dart';
import '../providers/dacha_provider.dart';
import '../providers/favorite_provider.dart';
import '../providers/notification_provider.dart';

class ServerConfigDialog extends StatefulWidget {
  final bool isFirstLaunch;

  const ServerConfigDialog({super.key, this.isFirstLaunch = false});

  static Future<void> show(BuildContext context, {bool isFirstLaunch = false}) async {
    return showDialog(
      context: context,
      barrierDismissible: !isFirstLaunch,
      builder: (context) => ServerConfigDialog(isFirstLaunch: isFirstLaunch),
    );
  }

  @override
  State<ServerConfigDialog> createState() => _ServerConfigDialogState();
}

class _ServerConfigDialogState extends State<ServerConfigDialog> {
  late final TextEditingController _urlController;
  bool _isTesting = false;
  bool? _testSuccess;
  String? _testMessage;

  @override
  void initState() {
    super.initState();
    final currentUrl = ApiClient().baseUrl;
    _urlController = TextEditingController(text: currentUrl);
  }

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }

  Future<void> _testConnection() async {
    final text = _urlController.text.trim();
    if (text.isEmpty) return;

    setState(() {
      _isTesting = true;
      _testSuccess = null;
      _testMessage = null;
    });

    final success = await ApiClient().testConnection(text);

    if (mounted) {
      setState(() {
        _isTesting = false;
        _testSuccess = success;
        _testMessage = success
            ? '✅ Serverga muvaffaqiyatli ulandi!'
            : '❌ Serverga ulanib bo\'lmadi. IP va portni tekshiring.';
      });
    }
  }

  Future<void> _saveAndApply() async {
    final text = _urlController.text.trim();
    if (text.isEmpty) return;

    await ApiClient().setBaseUrl(text);

    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('has_configured_server', true);

    if (mounted) {
      // Refresh providers
      context.read<DachaProvider>().fetchDachas();
      context.read<DachaProvider>().fetchAmenities();
      context.read<AuthProvider>().checkAuth();
      context.read<NotificationProvider>().fetchNotifications();
      context.read<FavoriteProvider>().fetchFavorites();

      Navigator.of(context).pop();

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Server manzili saqlandi: ${ApiClient().baseUrl}'),
          backgroundColor: AppColors.success,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _resetToDefault() async {
    await ApiClient().resetBaseUrl();
    setState(() {
      _urlController.text = ApiConstants.baseUrl;
      _testSuccess = null;
      _testMessage = null;
    });
  }

  void _usePreset(String url) {
    setState(() {
      _urlController.text = url;
      _testSuccess = null;
      _testMessage = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      backgroundColor: Colors.white,
      surfaceTintColor: Colors.white,
      insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.primaryLight,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(Icons.dns_rounded, color: AppColors.primary, size: 24),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Server Sozlamasi',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.dark),
                      ),
                      Text(
                        widget.isFirstLaunch ? 'Ilova uchun REST API manzilini tasdiqlang' : 'REST API Base URL',
                        style: const TextStyle(fontSize: 12, color: AppColors.textMuted),
                      ),
                    ],
                  ),
                ),
                if (!widget.isFirstLaunch)
                  IconButton(
                    icon: const Icon(Icons.close, color: AppColors.textMuted, size: 20),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
              ],
            ),

            const SizedBox(height: 20),

            // Info note
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
              ),
              child: const Row(
                children: [
                  Icon(Icons.info_outline_rounded, color: AppColors.primary, size: 20),
                  SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Lokal Wi-Fi yoki server IP manzilini kiriting. Har safar kiritganingiz xotirada saqlanadi.',
                      style: TextStyle(fontSize: 12, color: AppColors.darkLight, height: 1.4),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Quick Preset Chips
            const Text(
              'Tezkor namunalar:',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textMuted),
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 6,
              children: [
                ActionChip(
                  label: const Text('Wi-Fi (172.16.6.102:10017)', style: TextStyle(fontSize: 11)),
                  backgroundColor: AppColors.primaryLight,
                  onPressed: () => _usePreset('http://172.16.6.102:10017/api'),
                ),
                ActionChip(
                  label: const Text('Emulator (10.0.2.2:10017)', style: TextStyle(fontSize: 11)),
                  backgroundColor: AppColors.bgSurface,
                  onPressed: () => _usePreset('http://10.0.2.2:10017/api'),
                ),
                ActionChip(
                  label: const Text('Localhost (127.0.0.1:10017)', style: TextStyle(fontSize: 11)),
                  backgroundColor: AppColors.bgSurface,
                  onPressed: () => _usePreset('http://127.0.0.1:10017/api'),
                ),
              ],
            ),

            const SizedBox(height: 16),

            // Base URL Input
            const Text(
              'Base URL:',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.dark),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _urlController,
              keyboardType: TextInputType.url,
              style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600, color: AppColors.dark),
              decoration: InputDecoration(
                hintText: 'http://172.16.6.102:10017/api',
                prefixIcon: const Icon(Icons.link_rounded, color: AppColors.textMuted, size: 20),
                suffixIcon: IconButton(
                  icon: const Icon(Icons.clear, size: 18, color: AppColors.textMuted),
                  onPressed: () => _urlController.clear(),
                ),
              ),
            ),

            if (_testMessage != null) ...[
              const SizedBox(height: 10),
              AnimatedContainer(
                duration: const Duration(milliseconds: 300),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: _testSuccess == true ? AppColors.successLight : AppColors.errorLight,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        _testMessage!,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: _testSuccess == true ? AppColors.success : AppColors.error,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 20),

            // Action Buttons
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: _isTesting ? null : _testConnection,
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      side: const BorderSide(color: AppColors.primary),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    icon: _isTesting
                        ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                        : const Icon(Icons.network_check_rounded, size: 18, color: AppColors.primary),
                    label: const Text(
                      'Tekshirish',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.primary),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _saveAndApply,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    child: const Text(
                      'Saqlash & Kirish',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800),
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 10),

            // Reset to default button
            Center(
              child: TextButton.icon(
                onPressed: _resetToDefault,
                icon: const Icon(Icons.restore_rounded, size: 16, color: AppColors.textMuted),
                label: const Text(
                  'Asliga qaytarish (Default)',
                  style: TextStyle(fontSize: 12, color: AppColors.textMuted),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
