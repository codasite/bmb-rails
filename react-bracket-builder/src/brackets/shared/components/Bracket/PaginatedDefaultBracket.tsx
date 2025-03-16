import { useContext, useState } from 'react'
import { PaginatedBracketProps } from '../types'
import {
  getFirstRoundMatchGap as getDefaultFirstRoundMatchGap,
  getSubsequentMatchGap as getDefaultSubsequentMatchGap,
  getTeamGap as getDefaultTeamGap,
  getTeamHeight as getDefaultTeamHeight,
  getTeamWidth as getDefaultTeamWidth,
} from './utils'
import { DefaultMatchColumn } from '../MatchColumn'
import { BaseTeamSlot } from '../TeamSlot'
import { BracketLines, RootMatchLines } from './BracketLines'
import { DarkModeContext } from '../../context/context'
import { WinnerContainer } from '../MatchBox/Children/WinnerContainer'
import { DefaultNavButtons } from './BracketActionButtons'
import { MatchTree } from '../../models/MatchTree'
import { MatchNode } from '../../models/operations/MatchNode'

const getDefaultFirstPage = (matchTree: MatchTree) => {
  if (!matchTree.anyPicked()) {
    return 0
  }
  if (matchTree.isVoting) {
    return matchTree.liveRoundIndex * 2
  }
  if (matchTree.allPicked()) {
    return (matchTree.rounds.length - 1) * 2
  }
  // find first unpicked match
  const firstUnpickedMatch = matchTree.findMatch(
    (match) => match && !match.isPicked()
  )
  if (!firstUnpickedMatch) {
    return 0
  }
  const { roundIndex, matchIndex } = firstUnpickedMatch
  const numMatches = matchTree.rounds[roundIndex].matches.length
  let pageNum = roundIndex * 2
  if (matchIndex >= numMatches / 2) {
    pageNum++
  }
  return pageNum
}

const defaultHasNext = (matchTree: MatchTree, currentPage: number) => {
  if (matchTree.isVoting) {
    return currentPage < matchTree.liveRoundIndex * 2 + 1
  }
  return currentPage < (matchTree.rounds.length - 1) * 2
}

const defaultDisableNext = (visibleMatches: MatchNode[]) => {
  const someMatchNotPicked = visibleMatches.some(
    (match) => match && !match.isPicked()
  )
  return someMatchNotPicked
}

export const PaginatedDefaultBracket = (props: PaginatedBracketProps) => {
  const {
    getBracketWidth = () => 260,
    getTeamHeight = () => getDefaultTeamHeight(4),
    getTeamGap = () => getDefaultTeamGap(0),
    getFirstRoundMatchGap = () => getDefaultFirstRoundMatchGap(5),
    getSubsequentMatchGap = getDefaultSubsequentMatchGap,
    getTeamWidth = () => getDefaultTeamWidth(4),
    matchTree,
    setMatchTree,
    MatchColumnComponent = DefaultMatchColumn,
    MatchBoxComponent,
    TeamSlotComponent = BaseTeamSlot,
    onTeamClick,
    lineStyle,
    onFinished,
    NavButtonsComponent = DefaultNavButtons,
    getFirstPage = getDefaultFirstPage,
    hasNext = defaultHasNext,
    disableNext = defaultDisableNext,
  } = props
  const [page, setPage] = useState(getFirstPage(matchTree))

  const numRounds = matchTree.rounds.length
  const roundIndex = Math.floor(page / 2)
  const nextRoundIndex = roundIndex + 1
  const isLastRound = roundIndex === numRounds - 1
  const nextRoundIsLast = nextRoundIndex === numRounds - 1
  const isLeftSide = page % 2 === 0

  let currentRoundMatches = matchTree.rounds[roundIndex].matches
  let nextRoundMatches = isLastRound
    ? null
    : matchTree.rounds[nextRoundIndex].matches

  if (nextRoundMatches) {
    // remove nulls from col 1 whose parent match has no children
    currentRoundMatches = currentRoundMatches.reduce((acc, match, i) => {
      const parentMatchIndex = Math.floor(i / 2)
      const parentMatch = nextRoundMatches[parentMatchIndex]
      if (parentMatch.left || parentMatch.right) {
        acc = [...acc, match]
      }
      return acc
    }, [])
    // remove matches from col 2 with no children
    nextRoundMatches = nextRoundMatches.filter(
      (match) => match.left || match.right
    )
  }

  if (!isLastRound) {
    const mid1 = currentRoundMatches.length / 2
    const mid2 = nextRoundMatches.length / 2

    if (isLeftSide) {
      // if left side, get first half of matches
      currentRoundMatches = currentRoundMatches.slice(0, mid1)
      nextRoundMatches = nextRoundIsLast
        ? nextRoundMatches
        : nextRoundMatches.slice(0, mid2)
    } else {
      // if right side, get second half of matches
      currentRoundMatches = currentRoundMatches.slice(mid1)
      nextRoundMatches = nextRoundIsLast
        ? nextRoundMatches
        : nextRoundMatches.slice(mid2)
    }
  }

  const depth = numRounds - roundIndex - 1
  const matchGap1 = getFirstRoundMatchGap(numRounds)
  const teamGap = getTeamGap(depth)
  const teamHeight = getTeamHeight(numRounds)
  const teamWidth = getTeamWidth(numRounds)

  const matchHeight = teamHeight * 2 + teamGap
  const matchGap2 = getSubsequentMatchGap(matchHeight, matchGap1, matchHeight)

  const currentMatchPosition = isLeftSide ? 'left' : 'right'
  const nextMatchPosition = nextRoundIsLast
    ? 'center'
    : isLeftSide
    ? 'left'
    : 'right'

  const currentRoundColumn = (
    <MatchColumnComponent
      matches={currentRoundMatches}
      matchPosition={currentMatchPosition}
      matchTree={matchTree}
      setMatchTree={setMatchTree}
      MatchBoxComponent={MatchBoxComponent}
      TeamSlotComponent={TeamSlotComponent}
      matchGap={matchGap1}
      teamGap={teamGap}
      teamHeight={teamHeight}
      teamWidth={teamWidth}
      onTeamClick={onTeamClick}
    />
  )

  const nextRoundColumn = isLastRound ? null : (
    <MatchColumnComponent
      matches={nextRoundMatches}
      matchPosition={nextMatchPosition}
      matchTree={matchTree}
      MatchBoxComponent={MatchBoxComponent}
      TeamSlotComponent={(props) => (
        <div className={`tw-opacity-50`}>
          <TeamSlotComponent {...props} />
        </div>
      )}
      matchGap={matchGap2}
      teamGap={teamGap}
      teamHeight={teamHeight}
      teamWidth={teamWidth}
    />
  )

  const { darkMode } = useContext(DarkModeContext)

  const linesStyle = lineStyle || {
    className: `!tw-border-t-${darkMode ? 'white' : 'dd-blue'}`,
  }

  const handleNext = () => {
    const maxPages = (matchTree.rounds.length - 1) * 2
    const newPage = page + 1
    if (newPage <= maxPages) {
      setPage(newPage)
    }
  }
  const handlePrev = () => {
    const newPage = page - 1
    if (newPage >= 0) {
      setPage(newPage)
    }
  }
  const disablePrev = () => {
    if (matchTree.isVoting) {
      return page <= matchTree.liveRoundIndex * 2
    }
    return page <= 0
  }

  return (
    <div className={`tw-flex tw-flex-col tw-gap-48 tw-w-full tw-items-center`}>
      <div className="tw-flex tw-justify-center">
        <h2 className="tw-text-24 tw-font-700 !tw-text-dd-blue dark:!tw-text-white">{`Round ${
          roundIndex + 1
        }`}</h2>
      </div>
      <div
        className={`tw-flex tw-flex-col tw-justify-center tw-grow tw-gap-30${
          isLastRound ? ' tw-pb-0' : ''
        }`}
        style={{ width: getBracketWidth(numRounds) }}
      >
        {isLastRound && (
          <WinnerContainer
            match={matchTree.rounds[roundIndex].matches[0]}
            matchTree={matchTree}
            title="Winner"
            TeamSlotComponent={TeamSlotComponent}
            gap={16}
            titleFontSize={64}
          />
        )}

        <div
          className={`tw-flex tw-justify-${
            isLastRound ? 'center' : 'between'
          } tw-relative`}
        >
          {isLeftSide ? currentRoundColumn : nextRoundColumn}
          {isLeftSide ? nextRoundColumn : currentRoundColumn}
          {isLastRound ? (
            <RootMatchLines rounds={matchTree.rounds} style={linesStyle} />
          ) : (
            <BracketLines rounds={matchTree.rounds} style={linesStyle} />
          )}
        </div>
      </div>
      <NavButtonsComponent
        disableNext={disableNext(currentRoundMatches)}
        disablePrev={disablePrev()}
        onNext={handleNext}
        hasNext={hasNext(matchTree, page)}
        onPrev={handlePrev}
        onFullBracket={onFinished}
        onFinished={onFinished}
      />
    </div>
  )
}
