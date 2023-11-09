import React, { useContext, useEffect } from 'react'
import { PaginatedDefaultBracketProps } from '../types'
import {
  getFirstRoundMatchGap as getDefaultFirstRoundMatchGap,
  getSubsequentMatchGap as getDefaultSubsequentMatchGap,
  getTeamGap as getDefaultTeamGap,
  getTeamHeight as getDefaultTeamHeight,
  getTeamWidth as getDefaultTeamWidth,
  someMatchNotPicked,
} from './utils'
import { DefaultMatchColumn } from '../MatchColumn'
import { DefaultTeamSlot } from '../TeamSlot'
import { BracketLines, RootMatchLines } from './BracketLines'
import { DarkModeContext } from '../../context/context'
import { WinnerContainer } from '../MatchBox/Children/WinnerContainer'
import {
  DefaultFinalButton,
  DefaultNavButtons,
  DefaultNextButton,
} from './BracketActionButtons'

export const PaginatedDefaultBracket = (
  props: PaginatedDefaultBracketProps
) => {
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
    TeamSlotComponent = DefaultTeamSlot,
    onTeamClick,
    lineStyle,
    onFinished,
    NavButtonsComponent = DefaultNavButtons,
    page,
    setPage,
    forcePageAllPicked = true,
  } = props

  useEffect(() => {
    const paged = matchTree
    // try to determine page from matchTree
    if (!paged.anyPicked()) {
      return
    }
    if (paged.allPicked()) {
      return setPage((paged.rounds.length - 1) * 2)
    }
    // find first unpicked match
    const firstUnpickedMatch = paged.findMatch(
      (match) => match && !match.isPicked()
    )
    if (!firstUnpickedMatch) {
      return
    }
    const { roundIndex, matchIndex } = firstUnpickedMatch
    const numMatches = paged.rounds[roundIndex].matches.length
    let pageNum = roundIndex * 2
    if (matchIndex >= numMatches / 2) {
      pageNum++
    }
    setPage(pageNum)
  }, [])

  const numRounds = matchTree.rounds.length
  const roundIndex = Math.floor(page / 2)
  const nextRoundIndex = roundIndex + 1
  const currentRoundIsLast = roundIndex === numRounds - 1
  const nextRoundIsLast = nextRoundIndex === numRounds - 1
  const leftSide = page % 2 === 0

  let currentRoundMatches = matchTree.rounds[roundIndex].matches
  let nextRoundMatches = currentRoundIsLast
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

  if (!currentRoundIsLast) {
    const mid1 = currentRoundMatches.length / 2
    const mid2 = nextRoundMatches.length / 2

    if (leftSide) {
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

  const currentMatchPosition = leftSide ? 'left' : 'right'
  const nextMatchPosition = nextRoundIsLast
    ? 'center'
    : leftSide
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

  const nextRoundColumn = currentRoundIsLast ? null : (
    <MatchColumnComponent
      matches={nextRoundMatches}
      matchPosition={nextMatchPosition}
      matchTree={matchTree}
      MatchBoxComponent={MatchBoxComponent}
      TeamSlotComponent={TeamSlotComponent}
      matchGap={matchGap2}
      teamGap={teamGap}
      teamHeight={teamHeight}
      teamWidth={teamWidth}
    />
  )

  const darkMode = useContext(DarkModeContext)

  const linesStyle = lineStyle || {
    className: `!tw-border-t-${darkMode ? 'white' : 'dd-blue'}`,
  }

  const maxW = getBracketWidth(numRounds)

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

  return (
    <div
      className={`tw-flex tw-flex-col tw-gap-48 tw-min-h-screen tw-w-[${maxW}px] tw-m-auto tw-py-60`}
    >
      <div className="tw-flex tw-justify-center">
        <h2 className="tw-text-24 tw-font-700 tw-text-white">{`Round ${
          roundIndex + 1
        }`}</h2>
      </div>
      <div
        className={`tw-flex-grow tw-flex tw-flex-col tw-justify-center tw-gap-30${
          currentRoundIsLast ? ' tw-pb-0' : ''
        }`}
      >
        {currentRoundIsLast && (
          <WinnerContainer
            match={matchTree.rounds[roundIndex].matches[0]}
            matchTree={matchTree}
            topText="Winner"
            TeamSlotComponent={TeamSlotComponent}
            gap={16}
            topTextFontSize={64}
          />
        )}

        <div
          className={`tw-flex tw-justify-${
            currentRoundIsLast ? 'center' : 'between'
          } tw-relative`}
        >
          {leftSide ? currentRoundColumn : nextRoundColumn}
          {leftSide ? nextRoundColumn : currentRoundColumn}
          {currentRoundIsLast ? (
            <RootMatchLines rounds={matchTree.rounds} style={linesStyle} />
          ) : (
            <BracketLines rounds={matchTree.rounds} style={linesStyle} />
          )}
        </div>
      </div>
      <div
        className={`tw-flex tw-flex-col tw-justify-end tw-items-${
          currentRoundIsLast ? 'center' : 'stretch'
        }${currentRoundIsLast ? ' tw-flex-grow' : ''}`}
      >
        <NavButtonsComponent
          disableNext={
            forcePageAllPicked ? someMatchNotPicked(currentRoundMatches) : false
          }
          disablePrev={page === 0}
          onNext={handleNext}
          hasNext={page < (matchTree.rounds.length - 1) * 2}
          onPrev={handlePrev}
          onFullBracket={onFinished}
          onFinished={onFinished}
        />
      </div>
    </div>
  )
}
