export enum NotificationType {
  BracketUpcoming = 'bracket_upcoming',
}
export interface NotificationReq {
  id?: number
  postId: number
  userId?: number
  notificationType: NotificationType
}
