import { WildcardPlacement } from '../../models/WildcardPlacement'
import { BracketRes } from './bracket'

export const bracketResFactory = (
  override: Partial<BracketRes>
): BracketRes => {
  return {
    id: 1,
    title: 'Test Bracket',
    status: 'live',
    month: 'March',
    year: '2022',
    numTeams: 8,
    wildcardPlacement: WildcardPlacement.Top,
    author: 1,
    authorDisplayName: 'Test User',
    publishedDate: {
      date: '2024-08-28 16:46:09.000000',
      timezone_type: 1,
      timezone: '+00:00',
    },
    ...override,
  }
}
