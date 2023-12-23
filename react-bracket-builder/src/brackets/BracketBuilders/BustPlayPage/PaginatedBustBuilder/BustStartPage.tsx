import React, { useContext } from 'react'
import darkBracketBg from '../../../shared/assets/bracket-bg-dark.png'
import { MatchTree } from '../../../shared/models/MatchTree'
import { PickableBracket } from '../../../shared/components/Bracket'
import { ScaledBracket } from '../../../shared/components/Bracket/ScaledBracket'
import { BustablePlayPageButtons } from '../buttons'
import { ProfilePicture } from '../../../shared/components/ProfilePicture'
import { BracketMetaContext } from '../../../shared/context/context'

interface BustStartPageProps {
  handlePlayBracket: () => void
  handleAddApparel: () => void
  handleBustPlay: () => void
  thumbnailUrl: string
  matchTree?: MatchTree
  screenWidth: number
}

export const BustStartPage = (props: BustStartPageProps) => {
  const {
    handlePlayBracket,
    handleAddApparel,
    handleBustPlay,
    matchTree,
    screenWidth,
  } = props

  const { title } = useContext(BracketMetaContext)

  return (
    <div
      className={`wpbb-reset tw-flex tw-uppercase tw-min-h-screen tw-bg-no-repeat tw-bg-top tw-bg-cover tw-dark `}
      style={{ backgroundImage: `url(${darkBracketBg})` }}
    >
      <div
        className="tw-flex tw-flex-col tw-gap-30 tw-pt-[79px] tw-pb-20 tw-px-20 tw-items-center"
        style={{ maxWidth: screenWidth }}
      >
        <ProfilePicture
          src={props.thumbnailUrl}
          alt="celebrity-photo"
          color="blue"
          shadow={false}
        />
        <h1 className="tw-text-white tw-font-700 tw-text-24 tw-text-center">
          {title}
        </h1>

        {matchTree && (
          <ScaledBracket
            BracketComponent={PickableBracket}
            matchTree={matchTree}
            title=""
            windowWidth={screenWidth}
            paddingX={20}
          />
        )}
        <BustablePlayPageButtons
          handleBustPlay={handleBustPlay}
          handleAddApparel={handleAddApparel}
          handlePlayBracket={handlePlayBracket}
          size="small"
        />
      </div>
    </div>
  )
}
