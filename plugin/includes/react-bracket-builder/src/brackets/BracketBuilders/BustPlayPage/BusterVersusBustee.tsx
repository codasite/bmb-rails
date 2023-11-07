import { useContext } from 'react'
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
    <div className="tw-h-[140px] tw-flex tw-flex-col tw-justify-center tw-items-center">
      <div className="tw-text-2xl tw-font-bold tw-text-white tw-flex tw-flex-row">
        <div className="tw-mb-40 tw-mt-40 tw-flex tw-flex-col tw-justify-center tw-items-center">
          <ProfilePicture
            src={busteeThumbnail}
            alt="celebrity-photo"
            color="blue"
            shadow={true}
          />
          <span className="tw-text-white tw-font-700 tw-text-12 tw-mb-8 tw-mt-8">
            {busteeDisplayName}
          </span>
        </div>
        <span className="tw-text-white tw-font-700 tw-text-48 tw-m-18 tw-mt-40 tw-mb-40">
          VS
        </span>
        <div className="tw-mb-40 tw-mt-40 tw-flex tw-flex-col tw-justify-center tw-items-center">
          <ProfilePicture
            src={busterThumbnail}
            alt="celebrity-photo"
            color="red"
            backgroundColor="red/15"
            shadow={false}
          />
          <span className="tw-text-white tw-font-700 tw-text-12 tw-m-8">
            {busterDisplayName}
          </span>
        </div>
      </div>
    </div>
  )
}
