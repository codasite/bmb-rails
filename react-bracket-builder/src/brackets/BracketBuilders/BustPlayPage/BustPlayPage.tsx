import { useContext, useEffect, useState } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
import {
  WithBracketMeta,
  WithDarkMode,
} from '../../shared/components/HigherOrder'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { BracketMeta } from '../../shared/context/context'
import { BustPlayBuilder } from './BustPlayBuilder'
import { ProfilePicture } from '../../shared/components/ProfilePicture'
import { PlayRes } from '../../shared/api/types/bracket'
import {
  getBracketMeta,
  getBracketWidth,
} from '../../shared/components/Bracket/utils'
import { WithMatchTree3 } from '../../shared/components/HigherOrder/WithMatchTree'
import { getBustTrees } from './utils'
import { BustablePlayPageButtons } from './buttons'
import { addExistingPlayToApparelHandler } from '../ViewPlayPage/addExistingPlayToApparel'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'
import { BustStartPage } from './PaginatedBustBuilder/BustStartPage'
import { WithWindowDimensions } from '../../shared/components/HigherOrder/WithWindowDimensions'
import { WindowDimensionsContext } from '../../shared/context/WindowDimensionsContext'

interface BustPlayPageProps {
  bracketMeta: BracketMeta
  setBracketMeta: (bracketMeta: BracketMeta) => void
  bracketPlay: PlayRes
  darkMode: boolean
  setDarkMode: (darkMode: boolean) => void
  addApparelUrl: string
  myPlayHistoryUrl: string
}

const BustPlayPage = (props: BustPlayPageProps) => {
  const {
    setBracketMeta,
    darkMode,
    bracketPlay: play,
    addApparelUrl,
    myPlayHistoryUrl,
  } = props

  const [page, setPage] = useState('view')
  const { busteeTree, setBusteeTree } = getBustTrees()
  const thumbnailUrl = play?.thumbnailUrl

  const { height: windowHeight, width: windowWidth } = useContext(
    WindowDimensionsContext
  )

  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(play?.bracket?.numTeams))

  const canBust = play?.isBustable

  const handleBustPlay = async () => {
    setPage('bust')
  }

  const handlePlayBracket = async () => {
    window.location.href = play?.bracket?.url
  }

  const handleAddApparel = async () => {
    await addExistingPlayToApparelHandler(play?.id)
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
        redirectUrl={myPlayHistoryUrl}
        busteePlay={play}
        bracket={play?.bracket}
      />
    )
  }

  if (showPaginated) {
    return (
      <BustStartPage
        handleBustPlay={canBust ? handleBustPlay : undefined}
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
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover ${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${darkMode ? darkBracketBg : lightBracketBg})`,
      }}
    >
      <div
        className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto tw-pb-[83px] tw-pt-[62px] tw-gap-40`}
      >
        {busteeTree && (
          <>
            <div className="tw-flex tw-flex-col tw-justify-center tw-items-center">
              <ProfilePicture
                src={thumbnailUrl}
                alt="celebrity-photo"
                color="blue"
                shadow={false}
              />
            </div>
            <PickableBracket matchTree={busteeTree} />
            <BustablePlayPageButtons
              handleBustPlay={canBust ? handleBustPlay : undefined}
              handlePlayBracket={handlePlayBracket}
              handleAddApparel={handleAddApparel}
            />
          </>
        )}
      </div>
    </div>
  )
}

const WrappedBustPlayPage = WithWindowDimensions(
  WithMatchTree3(WithBracketMeta(WithDarkMode(BustPlayPage)))
)
export default WrappedBustPlayPage
