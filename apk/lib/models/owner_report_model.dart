class ReportSummaryModel {
  final double totalIncomeUzs;
  final double totalIncomeUsd;
  final int totalBookings;
  final int confirmedBookings;
  final int pendingBookings;
  final int cancelledBookings;
  final int totalBookedDays;
  final double occupancyRate;

  ReportSummaryModel({
    required this.totalIncomeUzs,
    required this.totalIncomeUsd,
    required this.totalBookings,
    required this.confirmedBookings,
    required this.pendingBookings,
    required this.cancelledBookings,
    required this.totalBookedDays,
    required this.occupancyRate,
  });

  factory ReportSummaryModel.fromJson(Map<String, dynamic> json) {
    return ReportSummaryModel(
      totalIncomeUzs: double.tryParse(json['total_income_uzs']?.toString() ?? '0') ?? 0.0,
      totalIncomeUsd: double.tryParse(json['total_income_usd']?.toString() ?? '0') ?? 0.0,
      totalBookings: int.tryParse(json['total_bookings']?.toString() ?? '0') ?? 0,
      confirmedBookings: int.tryParse(json['confirmed_bookings']?.toString() ?? '0') ?? 0,
      pendingBookings: int.tryParse(json['pending_bookings']?.toString() ?? '0') ?? 0,
      cancelledBookings: int.tryParse(json['cancelled_bookings']?.toString() ?? '0') ?? 0,
      totalBookedDays: int.tryParse(json['total_booked_days']?.toString() ?? '0') ?? 0,
      occupancyRate: double.tryParse(json['occupancy_rate']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class ReportSourceModel {
  final String label;
  final String icon;
  final int count;
  final double incomeUzs;
  final double incomeUsd;

  ReportSourceModel({
    required this.label,
    required this.icon,
    required this.count,
    required this.incomeUzs,
    required this.incomeUsd,
  });

  factory ReportSourceModel.fromJson(Map<String, dynamic> json) {
    return ReportSourceModel(
      label: json['label'] ?? '',
      icon: json['icon'] ?? '📊',
      count: int.tryParse(json['count']?.toString() ?? '0') ?? 0,
      incomeUzs: double.tryParse(json['income_uzs']?.toString() ?? '0') ?? 0.0,
      incomeUsd: double.tryParse(json['income_usd']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class ReportMonthlyTrendModel {
  final String monthKey;
  final String monthName;
  final int bookingsCount;
  final double incomeUzs;
  final double incomeUsd;

  ReportMonthlyTrendModel({
    required this.monthKey,
    required this.monthName,
    required this.bookingsCount,
    required this.incomeUzs,
    required this.incomeUsd,
  });

  factory ReportMonthlyTrendModel.fromJson(Map<String, dynamic> json) {
    return ReportMonthlyTrendModel(
      monthKey: json['month_key'] ?? '',
      monthName: json['month_name'] ?? '',
      bookingsCount: int.tryParse(json['bookings_count']?.toString() ?? '0') ?? 0,
      incomeUzs: double.tryParse(json['income_uzs']?.toString() ?? '0') ?? 0.0,
      incomeUsd: double.tryParse(json['income_usd']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class ReportDachaBreakdownModel {
  final int id;
  final String name;
  final String currency;
  final int bookingsCount;
  final double incomeUzs;
  final double incomeUsd;

  ReportDachaBreakdownModel({
    required this.id,
    required this.name,
    required this.currency,
    required this.bookingsCount,
    required this.incomeUzs,
    required this.incomeUsd,
  });

  factory ReportDachaBreakdownModel.fromJson(Map<String, dynamic> json) {
    return ReportDachaBreakdownModel(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name'] ?? '',
      currency: json['currency'] ?? 'USD',
      bookingsCount: int.tryParse(json['bookings_count']?.toString() ?? '0') ?? 0,
      incomeUzs: double.tryParse(json['income_uzs']?.toString() ?? '0') ?? 0.0,
      incomeUsd: double.tryParse(json['income_usd']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class OwnerReportModel {
  final String period;
  final String periodLabel;
  final String startDate;
  final String endDate;
  final ReportSummaryModel summary;
  final List<ReportSourceModel> sources;
  final List<ReportMonthlyTrendModel> monthlyTrend;
  final List<ReportDachaBreakdownModel> dachasBreakdown;

  OwnerReportModel({
    required this.period,
    required this.periodLabel,
    required this.startDate,
    required this.endDate,
    required this.summary,
    required this.sources,
    required this.monthlyTrend,
    required this.dachasBreakdown,
  });

  factory OwnerReportModel.fromJson(Map<String, dynamic> json) {
    return OwnerReportModel(
      period: json['period'] ?? 'this_month',
      periodLabel: json['period_label'] ?? '',
      startDate: json['start_date'] ?? '',
      endDate: json['end_date'] ?? '',
      summary: ReportSummaryModel.fromJson(json['summary'] ?? {}),
      sources: (json['sources'] as List<dynamic>? ?? [])
          .map((e) => ReportSourceModel.fromJson(e))
          .toList(),
      monthlyTrend: (json['monthly_trend'] as List<dynamic>? ?? [])
          .map((e) => ReportMonthlyTrendModel.fromJson(e))
          .toList(),
      dachasBreakdown: (json['dachas_breakdown'] as List<dynamic>? ?? [])
          .map((e) => ReportDachaBreakdownModel.fromJson(e))
          .toList(),
    );
  }
}
