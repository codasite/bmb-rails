import React, { useEffect, useState } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'
//@ts-ignore
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
//@ts-ignore
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { BracketMeta } from '../../shared/context/context'
import { BustPlayBuilder } from './BustPlayBuilder'
// import { ReactComponent as UserIcon } from '../../shared/assets/user.svg'
import { ProfilePicture } from '../../shared/components/ProfilePicture'
import { PlayRes } from '../../shared/api/types/bracket'
import { ReactComponent as LightningIcon } from '../../shared/assets/lightning.svg'
import { ReactComponent as PlayIcon } from '../../shared/assets/play.svg'
import {
  getBracketMeta,
  getBracketWidth,
} from '../../shared/components/Bracket/utils'
import { WithMatchTree3 } from '../../shared/components/HigherOrder/WithMatchTree'
import { getBustTrees } from './utils'
import { BustablePlayPageButtons } from './buttons'
import { addToApparelHandler } from '../ViewPlayPage/utils'
import { useWindowDimensions } from '../../../utils/hooks'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'
import { BustStartPage } from './PaginatedBustBuilder/BustStartPage'

interface BustPlayPageProps {
  bracketMeta: BracketMeta
  setBracketMeta: (bracketMeta: BracketMeta) => void
  bracketPlay: PlayRes
  redirectUrl: string
  darkMode: boolean
  setDarkMode: (darkMode: boolean) => void
  thumbnailUrl: string
}

const BustPlayPage = (props: BustPlayPageProps) => {
  const {
    bracketMeta,
    setBracketMeta,
    darkMode,
    setDarkMode,
    bracketPlay: play,
    redirectUrl,
    thumbnailUrl,
  } = props

  const [page, setPage] = useState('view')
  const { busteeTree, setBusteeTree } = getBustTrees()

  const { height: windowHeight, width: windowWidth } = useWindowDimensions()

  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(play?.bracket?.numTeams))

  const handleBustPlay = async () => {
    setPage('bust')
  }

  const handlePlayBracket = async () => {
    window.location.href = play?.bracket?.url
  }

  const handleAddApparel = async () => {
    addToApparelHandler(play?.id, 'test')
  }

  useEffect(() => {
    const picks = play?.picks
    const bracketTitle = play?.bracket?.title
    const authorDisplayName = play?.authorDisplayName
    const meta = getBracketMeta(play?.bracket)
    if (authorDisplayName) {
      meta.title = `${authorDisplayName}'s ${bracketTitle} picks`
    }
    setBracketMeta(meta)
    const bracket = play?.bracket
    const matches = bracket?.matches
    const numTeams = bracket?.numTeams
    if (picks && matches) {
      const tree = MatchTree.fromPicks(numTeams, matches, picks)

      if (tree) {
        setBusteeTree(tree)
      }
    }
  }, [play])

  if (page === 'bust' && busteeTree) {
    return (
      <BustPlayBuilder
        redirectUrl={redirectUrl}
        busteePlay={play}
        bracket={play?.bracket}
      />
    )
  }

  if (showPaginated) {
    return (
      <BustStartPage
        handleBustPlay={handleBustPlay}
        handlePlayBracket={handlePlayBracket}
        handleAddApparel={handleAddApparel}
        thumbnailUrl={thumbnailUrl}
        matchTree={busteeTree}
        screenWidth={windowWidth}
      />
    )
  }
  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${darkMode ? darkBracketBg : lightBracketBg})`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto tw-pb-[83px]`}
      >
        {busteeTree && (
          <>
            <div className="tw-mb-40 tw-mt-40 tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ProfilePicture
                src={thumbnailUrl}
                alt="celebrity-photo"
                color="blue"
                shadow={false}
              />
            </div>
            <PickableBracket matchTree={busteeTree} />
            <BustablePlayPageButtons
              handleBustPlay={handleBustPlay}
              handlePlayBracket={handlePlayBracket}
              handleAddApparel={handleAddApparel}
            />
          </>
        )}
      </div>
    </div>
  )
}

const WrappedBustPlayPage = WithProvider(
  WithMatchTree3(WithBracketMeta(WithDarkMode(BustPlayPage)))
)
export default WrappedBustPlayPage
