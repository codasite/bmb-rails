import { TeamSlotToggle } from '../../brackets/shared/components/TeamSlot'
import { TeamSlotProps } from '../../brackets/shared/components/types'

export const PopularityTeamSlot = (
  props: TeamSlotProps & {
    teamSlot?: React.ReactNode
    chipColor?: 'yellow' | 'green'
    loserChipColor?: 'yellow' | 'green' | 'gray'
    showLoserPopularity?: boolean
  }
) => {
  const {
    teamSlot = <TeamSlotToggle {...props} />,
    chipColor = 'yellow',
    showLoserPopularity = false,
    loserChipColor = 'gray',
  } = props
  const MarkerStyles = [
    'tw-absolute',
    'tw-top-0',
    'tw-right-0',
    'tw-transform',
    'tw-translate-x-1/2',
    'tw--translate-y-1/2',
    'tw-rounded-16',
    'tw-flex',
    'tw-items-center',
    'tw-justify-center',
  ]
  const isWinner = props.match._pick?.winningTeamId === props.team?.id

  // This could be a color helper function to get background colors
  const getChipColorClass = (chipColor: 'yellow' | 'green' | 'gray') => {
    switch (chipColor) {
      case 'yellow':
        return 'tw-bg-yellow'
      case 'green':
        return 'tw-bg-green'
      case 'gray':
        return 'tw-bg-[lightgray]'
    }
  }
  if (isWinner) {
    MarkerStyles.push(getChipColorClass(chipColor))
  } else {
    MarkerStyles.push(getChipColorClass(loserChipColor))
  }

  let percentage = null
  if (isNaN(props.match._pick?.popularity) || props.teamPosition === 'winner') {
    percentage = null
  } else if (isWinner) {
    percentage = props.match._pick.popularity
  } else if (showLoserPopularity) {
    percentage = 1 - props.match._pick.popularity
  }

  // Should eventually move chip into its own component
  return (
    <div className={`tw-relative`}>
      {teamSlot}
      <div className={MarkerStyles.join(' ')}>
        {percentage !== null && (
          <span className="tw-p-4 tw-text-dd-blue tw-text-12 tw-font-600 tw-leading-none">
            {(100 * percentage).toFixed(0).toString() + '%'}
          </span>
        )}
      </div>
    </div>
  )
}
