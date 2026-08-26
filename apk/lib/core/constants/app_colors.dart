import 'package:flutter/material.dart';

class AppColors {
  // Brand Primary & Accents
  static const Color primary = Color(0xFF0D9488);        // Emerald Teal
  static const Color primaryDark = Color(0xFF0F766E);
  static const Color primaryLight = Color(0xFFF0FDFA);
  static const Color primaryGlow = Color(0x400D9488);

  static const Color accent = Color(0xFFF59E0B);         // Amber Gold
  static const Color accentLight = Color(0xFFFEF3C7);

  // Neutrals & Backgrounds
  static const Color dark = Color(0xFF0F172A);           // Deep Slate
  static const Color darkLight = Color(0xFF1E293B);
  static const Color textMuted = Color(0xFF64748B);
  static const Color bgPage = Color(0xFFF8FAFC);         // Ultra-clean page background
  static const Color bgSurface = Color(0xFFFFFFFF);
  static const Color border = Color(0xFFE2E8F0);
  static const Color borderLight = Color(0xFFF1F5F9);

  // Status & Feedback
  static const Color success = Color(0xFF10B981);
  static const Color successLight = Color(0xFFDCFCE7);
  static const Color error = Color(0xFFEF4444);
  static const Color errorLight = Color(0xFFFEE2E2);
  static const Color warning = Color(0xFFF59E0B);
  static const Color info = Color(0xFF0EA5E9);
  static const Color infoLight = Color(0xFFE0F2FE);

  // Telegram Branding
  static const Color telegram = Color(0xFF229ED9);
  static const Color telegramDark = Color(0xFF0284C7);

  // Linear Gradients
  static const LinearGradient primaryGradient = LinearGradient(
    colors: [Color(0xFF0D9488), Color(0xFF065F46)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient accentGradient = LinearGradient(
    colors: [Color(0xFFF59E0B), Color(0xFFD97706)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient cardOverlay = LinearGradient(
    colors: [Colors.transparent, Color(0xCC0F172A)],
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );
}
