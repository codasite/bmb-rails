import { TeamSlotProps } from '../types'
import { getScoredPlayTrees } from '../../../BracketBuilders/ViewPlayPage/getScoredPlayTrees'
import { TeamSlotToggle } from './TeamSlotToggle'
import { ThickCheckIcon, XIcon } from '../../assets'
import { ActiveTeamSlot } from './ActiveTeamSlot'

const MarkerStyles = [
  'tw-absolute',
  'tw-top-0',
  'tw-right-0',
  'tw-transform',
  'tw-translate-x-1/2',
  'tw--translate-y-1/2',
  'tw-rounded-full',
  'tw-w-16',
  'tw-h-16',
  'tw-flex',
  'tw-items-center',
  'tw-justify-center',
]

const CorrectPickTeamSlot = (props: TeamSlotProps) => {
  const styles = [...MarkerStyles, 'tw-bg-green'].join(' ')
  return (
    <div className={`tw-relative`}>
      <ActiveTeamSlot {...props} />
      <div className={styles}>
        <ThickCheckIcon className="tw-h-12 tw-w-12 tw-text-black" />
      </div>
    </div>
  )
}

const IncorrectPickTeamSlot = (props: TeamSlotProps) => {
  const styles = [...MarkerStyles, 'tw-bg-red'].join(' ')
  return (
    <div className={`tw-relative`}>
      <ActiveTeamSlot {...props} />
      <div className={styles}>
        <XIcon className="tw-h-12 tw-w-12 tw-text-black" />
      </div>
    </div>
  )
}

export const ScoredPlayTeamSlot = (props: TeamSlotProps) => {
  const { team, match, teamPosition } = props
  const { resultsTree } = getScoredPlayTrees()

  // Don't show markers on winner
  if (teamPosition === 'winner') {
    return <TeamSlotToggle {...props} />
  }

  const roundIndex = match.roundIndex
  const matchIndex = match.matchIndex

  const playMatch = match
  const resultsMatch = resultsTree.rounds[roundIndex].matches[matchIndex]

  const playTeam = team
  const resultsTeam =
    teamPosition === 'left' ? resultsMatch.getTeam1() : resultsMatch.getTeam2()

  // whether the player picked this team
  const playPicked = playTeam && playMatch.getWinner() === playTeam
  // whether the results picked this team
  const resultsPicked = resultsTeam && resultsMatch.getWinner() === resultsTeam

  if (playPicked && resultsPicked) {
    // if play pick matches results, show green checkmark
    return <CorrectPickTeamSlot {...props} />
  }
  if (resultsMatch.getWinner() && playPicked && !resultsPicked) {
    // if results has a winner and play pick does not match, show red x
    return <IncorrectPickTeamSlot {...props} />
  }
  // if results team does not exist, show normal team slot
  return <TeamSlotToggle {...props} />
}
