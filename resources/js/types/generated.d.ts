declare namespace App {
namespace Domain {
namespace Analytics {
namespace Data {
export type DashboardSummaryData = {
eventsLast24h: number,
uniqueVisitors: number,
ingestP95Ms: number,
eventsOverTime: App.Domain.Analytics.Data.TimeBucketData[],
};
export type TimeBucketData = {
bucket: string,
count: number,
};
}
namespace Enums {
export type EventType = 'page_view' | 'click' | 'custom' | 'conversion';
export type TimeGranularity = 'minute' | 'hour' | 'day' | 'month';
}
}
}
}
