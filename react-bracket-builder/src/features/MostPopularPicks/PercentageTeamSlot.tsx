import { TeamSlotToggle } from '../../brackets/shared/components/TeamSlot'
import { TeamSlotProps } from '../../brackets/shared/components/types'

export const PercentageTeamSlot = (props: TeamSlotProps) => {
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
    'tw-bg-yellow',
  ]

  let percentage = 100

  return (
    <div className={`tw-relative`}>
      <TeamSlotToggle {...props} />
      <div className={MarkerStyles.join(' ')}>
        {percentage && (
          <span className="tw-p-4 tw-text-black tw-text-12 tw-font-600 tw-leading-none">
            {percentage.toFixed(0) + '%'}
          </span>
        )}
      </div>
    </div>
  )
}
