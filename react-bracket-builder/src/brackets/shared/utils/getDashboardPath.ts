import { wpbbAjax } from '../../../utils/WpbbAjax'
export const getDashboardPath = (
  role: 'hosting' | 'playing' = 'hosting',
  status: 'live' | 'private' | 'upcoming' | 'closed' = 'live'
) => {
  const { dashboardUrl } = wpbbAjax.getAppObj()
  if (!dashboardUrl) {
    return ''
  }
  return `${dashboardUrl}?role=${role}&status=${status}`
}
