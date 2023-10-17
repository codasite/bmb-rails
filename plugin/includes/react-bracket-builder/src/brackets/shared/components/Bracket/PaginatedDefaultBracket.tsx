import React, { useContext, useEffect } from 'react'
import { PaginatedBracketProps } from '../types'
import {
  getFirstRoundMatchGap as getDefaultFirstRoundMatchGap,
  getSubsequentMatchGap as getDefaultSubsequentMatchGap,
  getTeamFontSize as getDefaultTeamFontSize,
  getTeamGap as getDefaultTeamGap,
  getTeamHeight as getDefaultTeamHeight,
  getTeamWidth as getDefaultTeamWidth,
} from '../../utils'
import { DefaultMatchColumn } from '../MatchColumn'
import { DefaultTeamSlot } from '../TeamSlot'
import { BracketLines, RootMatchLines } from './BracketLines'
import { DarkModeContext } from '../../context'
import { WinnerContainer } from '../MatchBox/Children/WinnerContainer'
import { DefaultFinalButton, DefaultNextButton } from './BracketActionButtons'

export const PaginatedDefaultBracket = (props: PaginatedBracketProps) => {
  const {
    getBracketWidth = () => 260,
    getTeamHeight = () => getDefaultTeamHeight(4),
    getTeamGap = () => getDefaultTeamGap(0),
    getFirstRoundMatchGap = () => getDefaultFirstRoundMatchGap(5),
    getSubsequentMatchGap = getDefaultSubsequentMatchGap,
    getTeamFontSize = () => getDefaultTeamFontSize(4),
    getTeamWidth = () => getDefaultTeamWidth(4),
    matchTree,
    setMatchTree,
    MatchColumnComponent = DefaultMatchColumn,
    MatchBoxComponent,
    TeamSlotComponent = DefaultTeamSlot,
    onTeamClick,
    lineStyle,
    onFinished,
    NextButtonComponent = DefaultNextButton,
    FinalButtonComponent = DefaultFinalButton,
    page,
    setPage,
  } = props

  useEffect(() => {
    // try to determine page from matchTree
    if (!matchTree.anyPicked()) {
      return
    }
    if (matchTree.allPicked()) {
      return setPage((matchTree.rounds.length - 1) * 2)
    }
    // find first unpicked match
    const firstUnpickedMatch = matchTree.findMatch(
      (match) => match && !match.isPicked()
    )
    if (!firstUnpickedMatch) {
      return
    }
    const { roundIndex, matchIndex } = firstUnpickedMatch
    const numMatches = matchTree.rounds[roundIndex].matches.length
    let pageNum = roundIndex * 2
    if (matchIndex >= numMatches / 2) {
      pageNum++
    }
    setPage(pageNum)
  }, [])

  const numRounds = matchTree.rounds.length
  const roundIndex = Math.floor(page / 2)
  const nextRoundIndex = roundIndex + 1
  const thisRoundIsLast = roundIndex === numRounds - 1
  const nextRoundIsLast = nextRoundIndex === numRounds - 1
  const leftSide = page % 2 === 0

  let currentRoundMatches = matchTree.rounds[roundIndex].matches
  let nextRoundMatches = thisRoundIsLast
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

  if (!thisRoundIsLast) {
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
  const teamFontSize = getTeamFontSize(numRounds)

  const matchHeight = teamHeight * 2 + teamGap
  const matchGap2 = getSubsequentMatchGap(matchHeight, matchGap1, matchHeight)

  const thisMatchPosition = leftSide ? 'left' : 'right'
  const nextMatchPosition = nextRoundIsLast
    ? 'center'
    : leftSide
    ? 'left'
    : 'right'

  const matchCol1 = (
    <MatchColumnComponent
      matches={currentRoundMatches}
      matchPosition={thisMatchPosition}
      matchTree={matchTree}
      setMatchTree={setMatchTree}
      MatchBoxComponent={MatchBoxComponent}
      TeamSlotComponent={TeamSlotComponent}
      matchGap={matchGap1}
      teamGap={teamGap}
      teamHeight={teamHeight}
      teamWidth={teamWidth}
      teamFontSize={teamFontSize}
      onTeamClick={onTeamClick}
    />
  )

  const matchCol2 = thisRoundIsLast ? null : (
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
      teamFontSize={teamFontSize}
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
  const disableNext = currentRoundMatches.some(
    (match) => match && !match.isPicked()
  )

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
          thisRoundIsLast ? ' tw-pb-0' : ''
        }`}
      >
        {thisRoundIsLast && (
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
            thisRoundIsLast ? 'center' : 'between'
          }`}
        >
          {leftSide ? matchCol1 : matchCol2}
          {leftSide ? matchCol2 : matchCol1}
          {thisRoundIsLast ? (
            <RootMatchLines rounds={matchTree.rounds} style={linesStyle} />
          ) : (
            <BracketLines rounds={matchTree.rounds} style={linesStyle} />
          )}
        </div>
      </div>
      <div
        className={`tw-flex tw-flex-col tw-justify-end tw-items-${
          thisRoundIsLast ? 'center' : 'stretch'
        }${thisRoundIsLast ? ' tw-flex-grow' : ''}`}
      >
        {thisRoundIsLast ? (
          <FinalButtonComponent disabled={disableNext} onClick={onFinished} />
        ) : (
          <NextButtonComponent disabled={disableNext} onClick={handleNext} />
        )}
      </div>
    </div>
  )
}
