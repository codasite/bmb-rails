import { Round } from '../../models/Round'
import LineTo, { SteppedLineTo } from 'react-lineto'
import { getUniqueTeamClass } from './utils'

interface BracketLinesProps {
  rounds: Round[]
  style?: any
}

export const BracketLines = (props: BracketLinesProps) => {
  const { rounds, style } = props
  // Main function
  const renderLines = (rounds: Round[]): JSX.Element[] => {
    let lines: JSX.Element[] = []
    // Lines are always drawn from left to right so these two variables never change for horizontal lines
    const fromAnchor = 'right'
    const toAnchor = 'left'

    rounds.forEach((round) => {
      round.matches.forEach((match, i, matches) => {
        if (!match) {
          return
        }
        const { matchIndex, roundIndex, parent } = match

        if (!parent) {
          return
        }
        const { matchIndex: parentMatchIndex, roundIndex: parentRoundIndex } =
          parent

        const matchTeam1Class = getUniqueTeamClass(
          roundIndex,
          matchIndex,
          'left'
        )
        const matchTeam2Class = getUniqueTeamClass(
          roundIndex,
          matchIndex,
          'right'
        )
        const parentTeamClass = getUniqueTeamClass(
          parentRoundIndex,
          parentMatchIndex,
          `${match.isLeftChild() ? 'left' : 'right'}`
        )

        const bracketLeft = matchIndex < matches.length / 2

        const line1FromClass = bracketLeft ? matchTeam1Class : parentTeamClass
        const line1ToClass = bracketLeft ? parentTeamClass : matchTeam1Class
        const line2FromClass = bracketLeft ? matchTeam2Class : parentTeamClass
        const line2ToClass = bracketLeft ? parentTeamClass : matchTeam2Class

        lines = [
          ...lines,
          <SteppedLineTo
            key={`${line1FromClass}-${line1ToClass}`}
            from={line1FromClass}
            to={line1ToClass}
            fromAnchor={fromAnchor}
            toAnchor={toAnchor}
            orientation="h"
            delay={true}
            {...style}
          />,
          <SteppedLineTo
            key={`${line2FromClass}-${line2ToClass}`}
            from={line2FromClass}
            to={line2ToClass}
            fromAnchor={fromAnchor}
            toAnchor={toAnchor}
            orientation="h"
            delay={true}
            {...style}
          />,
        ]
      })
    })
    return lines
  }
  return <div className="tw-absolute">{renderLines(rounds)}</div>
}

export const RootMatchLines = (props: BracketLinesProps) => {
  const { rounds, style } = props

  const rootMatch = rounds[props.rounds.length - 1].matches[0]
  if (!rootMatch) {
    return null
  }
  const rootWinnerClass = getUniqueTeamClass(
    rootMatch.roundIndex,
    rootMatch.matchIndex,
    'winner'
  )
  const rootTeam1Class = getUniqueTeamClass(
    rootMatch.roundIndex,
    rootMatch.matchIndex,
    'left'
  )
  const rootTeam2Class = getUniqueTeamClass(
    rootMatch.roundIndex,
    rootMatch.matchIndex,
    'right'
  )

  return (
    <div className="tw-absolute" key={'jaiefji'}>
      <LineTo
        from={rootWinnerClass}
        to={rootTeam1Class}
        fromAnchor="bottom"
        toAnchor="top"
        delay={true}
        {...style}
      />
      <LineTo
        from={rootTeam1Class}
        to={rootTeam2Class}
        fromAnchor="bottom"
        toAnchor="top"
        delay={true}
        {...style}
      />
    </div>
  )
}
