class UserModel {
  final int id;
  final String name;
  final String? phone;
  final String? email;
  final String? avatar;
  final String role;
  final String? telegramId;

  UserModel({
    required this.id,
    required this.name,
    this.phone,
    this.email,
    this.avatar,
    required this.role,
    this.telegramId,
  });

  bool get isOwner => role == 'owner' || role == 'admin' || role == 'super_admin';
  bool get hasTelegram => telegramId != null && telegramId!.isNotEmpty;

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name'] ?? '',
      phone: json['phone'],
      email: json['email'],
      avatar: json['avatar'],
      role: json['role'] ?? 'user',
      telegramId: json['telegram_id']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'phone': phone,
      'email': email,
      'avatar': avatar,
      'role': role,
      'telegram_id': telegramId,
    };
  }
}
