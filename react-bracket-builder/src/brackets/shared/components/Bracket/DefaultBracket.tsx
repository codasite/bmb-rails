// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext, useEffect, useRef, useCallback } from 'react'
import {
  getBracketWidth as getDefaultBracketWidth,
  getFirstRoundMatchGap as getDefaultFirstRoundMatchGap,
  getSubsequentMatchGap as getDefaultSubsequentMatchGap,
  getTeamGap as getDefaultTeamGap,
  getTeamHeight as getDefaultTeamHeight,
  getTeamWidth as getDefaultTeamWidth,
} from './utils'
import { Nullable } from '../../../../utils/types'
import { BracketProps } from '../types'
import { BracketMetaContext, DarkModeContext } from '../../context/context'
import { DefaultMatchColumn } from '../MatchColumn'
import { BaseTeamSlot } from '../TeamSlot'
import { defaultBracketConstants } from '../../constants'
import { WinnerContainer } from '../MatchBox/Children/WinnerContainer'
import { LogoContainer } from '../MatchBox/Children/LogoContainer'
import { BracketLines, RootMatchLines } from './BracketLines'
import { MatchNode } from '../../models/operations/MatchNode'
import { Round } from '../../models/Round'
import {
  getFirstMatches,
  getFinalMatches,
  getSideMatches,
} from '../../models/operations/GetMatchSections'
import { SizeChangeListenerContext } from '../../context/SizeChangeListenerContext'
import { useResizeObserver } from '../../../../utils/hooks'

export const DefaultBracket = (props: BracketProps) => {
  const {
    getBracketWidth = getDefaultBracketWidth,
    getTeamHeight = getDefaultTeamHeight,
    getTeamWidth = getDefaultTeamWidth,
    getTeamGap = getDefaultTeamGap,
    getFirstRoundMatchGap = getDefaultFirstRoundMatchGap,
    getSubsequentMatchGap = getDefaultSubsequentMatchGap,
    matchTree,
    setMatchTree,
    MatchColumnComponent = DefaultMatchColumn,
    MatchBoxComponent,
    TeamSlotComponent = BaseTeamSlot,
    MatchBoxChildComponent,
    onTeamClick,
    lineStyle,
    lineColor = 'dd-blue',
    darkLineColor = 'white',
    lineWidth = 1,
    title,
    date,
    columnsToRender,
    renderWinnerAndLogo = true,
  }: BracketProps = props

  const containerRef = useRef(null)
  const { sizeChangeListeners } = useContext(SizeChangeListenerContext)

  const resizeCallback = useCallback(
    ({ height, width }) => {
      if (sizeChangeListeners && containerRef.current) {
        sizeChangeListeners?.forEach((listener) => {
          listener(height, width)
        })
      }
    },
    [sizeChangeListeners]
  )

  useResizeObserver(containerRef, resizeCallback)

  const { darkMode } = useContext(DarkModeContext)
  let bracketTitle = title
  let bracketDate = date
  if (!bracketTitle || !date) {
    const meta = useContext(BracketMetaContext)
    bracketTitle = title ?? meta?.title
    bracketDate = date ?? meta?.date
  }
  const linesStyle = lineStyle || {
    className: `!tw-border-t-[${lineWidth}px] !tw-border-t-${
      darkMode ? darkLineColor : lineColor
    }`,
  }

  const getBracketMeasurements = (roundIndex: number, numRounds: number) => {
    const teamHeight = getTeamHeight(numRounds)
    const teamWidth = getTeamWidth(numRounds)
    const teamGap = getTeamGap(numRounds - roundIndex - 1)
    const matchHeight = teamHeight * 2 + teamGap
    let matchGap: number
    if (roundIndex === 0) {
      matchGap = getFirstRoundMatchGap(numRounds)
    } else {
      const { matchHeight: prevMatchHeight, matchGap: prevMatchGap } =
        getBracketMeasurements(roundIndex - 1, numRounds)
      matchGap = getSubsequentMatchGap(
        prevMatchHeight,
        prevMatchGap,
        matchHeight
      )
    }

    return {
      teamHeight,
      teamWidth,
      teamGap,
      matchHeight,
      matchGap,
    }
  }

  const getMatchColumns = (
    rounds: Nullable<MatchNode>[][],
    position: 'first' | 'left' | 'right' | 'center',
    numRounds: number
  ): JSX.Element[] => {
    return rounds.map((matches, i) => {
      let roundIndex: number
      if (position === 'first') {
        roundIndex = i
      } else if (position === 'left') {
        roundIndex = i
      } else if (position === 'right') {
        roundIndex = numRounds - i - 2
      } else if (position === 'center') {
        roundIndex = numRounds - 1
      }
      const { teamHeight, teamWidth, teamGap, matchGap } =
        getBracketMeasurements(roundIndex, numRounds)
      return (
        <MatchColumnComponent
          key={`${position}-${i}`}
          matches={matches}
          matchPosition={position}
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          MatchBoxComponent={MatchBoxComponent}
          TeamSlotComponent={TeamSlotComponent}
          MatchBoxChildComponent={MatchBoxChildComponent}
          matchGap={matchGap}
          teamGap={teamGap}
          teamHeight={teamHeight}
          teamWidth={teamWidth}
          onTeamClick={onTeamClick}
        />
      )
    })
  }

  const buildMatches = (rounds: Round[]) => {
    // Build the left matches, right matches, and final match separately
    const firstMatches = getFirstMatches(rounds)
    const { left: leftMatches, right: rightMatches } = getSideMatches(rounds)
    const finalMatches = getFinalMatches(rounds)

    const firstMatchColumns = getMatchColumns(firstMatches, 'first', numRounds)
    const leftMatchColumns = getMatchColumns(leftMatches, 'left', numRounds)
    const rightMatchColumns = getMatchColumns(rightMatches, 'right', numRounds)
    const finalMatchColumn = getMatchColumns(finalMatches, 'center', numRounds)

    return [...leftMatchColumns, ...finalMatchColumn, ...rightMatchColumns].map(
      (column, index) => {
        if (!columnsToRender) {
          return column
        }
        if (columnsToRender.includes(index)) {
          return column
        }
        return <div className={`tw-w-[${getTeamWidth(numRounds)}px]`}></div>
      }
    )
  }

  const width = getBracketWidth(matchTree.rounds.length)

  const rootMatch = matchTree.getRootMatch()
  const numRounds = matchTree.rounds.length
  const winnerContainerMB =
    defaultBracketConstants.winnerContainerBottomMargin[numRounds]
  const winnerContainerMinHeight =
    defaultBracketConstants.winnerContainerMinHeight[numRounds]
  const logoContainerMT = numRounds > 5 ? 50 : 20
  const logoContainerMinHeight =
    defaultBracketConstants.logoContainerMinHeight[numRounds]

  return (
    <div
      className={`tw-flex tw-flex-col${
        darkMode ? ' tw-dark' : ''
      } wpbb-default-bracket tw-relative`}
      ref={containerRef}
    >
      {rootMatch && renderWinnerAndLogo && (
        <div
          className="tw-flex tw-flex-col tw-justify-end"
          style={{
            marginBottom: winnerContainerMB,
            minHeight: winnerContainerMinHeight,
          }}
        >
          <WinnerContainer
            match={rootMatch}
            matchTree={matchTree}
            matchPosition="center"
            TeamSlotComponent={TeamSlotComponent}
            topText={bracketTitle}
          />
        </div>
      )}
      <div
        className={`tw-flex tw-flex-col tw-justify-center tw-items-center ${
          renderWinnerAndLogo ? 'tw-h-100' : ''
        }`}
      >
        <div
          className={`tw-flex ${
            numRounds > 1 ? 'tw-justify-between' : 'tw-justify-center'
          }`}
          style={{ width: width }}
        >
          {buildMatches(matchTree.rounds)}
        </div>
      </div>
      {renderWinnerAndLogo && (
        <div
          style={{
            marginTop: logoContainerMT,
            minHeight: logoContainerMinHeight,
          }}
        >
          <LogoContainer {...props} bottomText={bracketDate} />
        </div>
      )}
      <BracketLines
        rounds={matchTree.rounds}
        style={linesStyle}
        getLineStyle={props.getLineStyle}
      />
      {renderWinnerAndLogo && (
        <RootMatchLines
          rounds={matchTree.rounds}
          style={linesStyle}
          getLineStyle={props.getLineStyle}
        />
      )}
    </div>
  )
}
