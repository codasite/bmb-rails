import { ProfilePicture } from '../../shared/components/ProfilePicture'

interface BusterVsBusteeProps {
  busteeDisplayName?: string
  busteeThumbnail?: string
  busterDisplayName?: string
  busterThumbnail?: string
}

export const BusterVsBustee = (props: BusterVsBusteeProps) => {
  const {
    busteeDisplayName = 'Opponent',
    busteeThumbnail = '',
    busterDisplayName = 'You',
    busterThumbnail = '',
  } = props
  return (
    <div className="tw-flex tw-gap-18 tw-justify-center tw-items-center">
      <div className="tw-flex tw-flex-col tw-items-center tw-gap-4">
        <ProfilePicture
          src={busteeThumbnail}
          alt="celebrity-photo"
          color="blue"
          shadow={true}
        />
        <span className="tw-text-white tw-font-700 tw-text-12">
          {busteeDisplayName}
        </span>
      </div>
      <span className="tw-text-white tw-font-700 tw-text-48">VS</span>
      <div className="tw-flex tw-flex-col tw-items-center tw-gap-4">
        <ProfilePicture
          src={busterThumbnail}
          alt="celebrity-photo"
          color="red"
          shadow={false}
        />
        <span className="tw-text-white tw-font-700 tw-text-12">
          {busterDisplayName}
        </span>
      </div>
    </div>
  )
}
