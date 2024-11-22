// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
//@ts-ignore
import { MatchColumnProps } from '../types'
//@ts-ignore
import { DefaultMatchBox } from '../MatchBox/DefaultMatchBox'
import { FlexMatchBox } from '../MatchBox'
import { isPowerOfTwo } from '../Bracket/utils'
import { flexBracketConstants } from '../../constants'
import { WildcardPlacement } from '../../models/WildcardPlacement'

const { teamBreakpoints, teamHeights, teamGaps, matchGapMin, matchGapMax } =
  flexBracketConstants

interface FlexMatchColumnProps {
  minHeight?: number
  maxHeight?: number
}

const FlexMatchGap = (props: FlexMatchColumnProps) => {
  const {
    minHeight = 4,
    // minHeight = 8,
    maxHeight = 16,
  } = props
  const minHeightClass = minHeight >= 0 ? ` tw-min-h-[${minHeight}px]` : ''
  const maxHeightClass = maxHeight >= 0 ? ` tw-max-h-[${maxHeight}px]` : ''
  return (
    <div
      className={`tw-flex-grow tw-flex-shrink tw-flex-basis-10 ${minHeightClass}${maxHeightClass}`}
    ></div>
  )
}

export const FlexMatchColumn = (props: MatchColumnProps) => {
  const {
    matches,
    matchPosition,
    matchTree,
    setMatchTree,
    MatchBoxComponent = FlexMatchBox,
    teamGap,
    matchGap,
    teamHeight,
    onTeamClick,
  } = props

  let justifyContent = 'tw-justify-center'

  const outerColumn = matches.find((match) => match !== null)?.roundIndex === 0
  const wildcardPlacement = matchTree.getWildcardPlacement()
  const numTeams = matchTree.getNumTeams()
  const teamBreakpointIndex = teamBreakpoints.findIndex(
    (breakpoint) => numTeams <= breakpoint
  )
  const minMatchGap = matchGapMin[teamBreakpointIndex]
  const maxMatchGap = matchGapMax[teamBreakpointIndex]
  // const wildcardPlacement = WildcardPlacement.Bottom

  if (outerColumn && !isPowerOfTwo(matchTree.getNumTeams())) {
    if (wildcardPlacement === WildcardPlacement.Top) {
      justifyContent = 'tw-justify-start'
    } else if (wildcardPlacement === WildcardPlacement.Bottom) {
      justifyContent = 'tw-justify-end'
    }
  }

  return (
    <div className={`tw-flex tw-flex-col ${justifyContent} tw-flex-grow`}>
      {matches.reduce((matchBoxes, match, index) => {
        if (!match) {
          if (outerColumn) {
            if (wildcardPlacement === WildcardPlacement.Split) {
              matchBoxes.push(<FlexMatchGap maxHeight={99} />)
            }
          }
          return matchBoxes
        }
        if (index > 0) {
          matchBoxes.push(
            <FlexMatchGap
              minHeight={minMatchGap}
              maxHeight={maxMatchGap}
              key={`gap-${index}`}
            />
          )
        }
        matchBoxes.push(
          <MatchBoxComponent
            match={match}
            matchPosition={matchPosition}
            matchTree={matchTree}
            setMatchTree={setMatchTree}
            teamGap={teamGap}
            teamHeight={teamHeight}
            onTeamClick={onTeamClick}
            key={index}
          />
        )
        return matchBoxes
      }, [] as JSX.Element[])}
    </div>
  )
}
