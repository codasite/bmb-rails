import { defaultBracketConstants } from '../../constants'
import { BracketRes } from '../../api/types/bracket'
import { BracketMeta } from '../../context/context'
import { MatchNode } from '../../models/operations/MatchNode'
import { Team } from '../../models/Team'

const {
  bracketHeights,
  teamHeights,
  teamWidths,
  teamGaps,
  bracketWidths,
  firstRoundsMatchGaps,
} = defaultBracketConstants

export const getBracketHeight = (numRounds: number) => {
  return bracketHeights[numRounds]
}

export const getBracketWidth = (numRounds: number) => {
  return bracketWidths[numRounds]
}

export const getTeamHeight = (numRounds: number) => {
  return teamHeights[numRounds]
}

export const getTeamWidth = (numRounds: number) => {
  return teamWidths[numRounds]
}

export const getTeamGap = (depth: number) => {
  return teamGaps[depth]
}

export const getTeamFontSize = (numRounds: number) => {
  if (numRounds > 4) {
    return 12
  }
  return 16
}

export const getTeamMinFontSize = (numRounds: number) => {
  return 10.5
}

export const getTeamPaddingX = (numRounds: number) => {
  if (numRounds > 4) {
    return 2
  }
  return 4
}

export const getFirstRoundMatchGap = (numRounds: number) => {
  return firstRoundsMatchGaps[numRounds]
}

export const getTeamClasses = (
  roundIndex: number,
  matchIndex: number,
  left: boolean
) => {
  const uniqueClass = getUniqueTeamClass(
    roundIndex,
    matchIndex,
    left ? 'left' : 'right'
  )
  const teamClass = getTeamClass(left)
  const className = `${teamClass} ${uniqueClass}`
  return className
}

const getTeamClass = (left: boolean) => {
  const teamClass = `wpbb-team${left ? '1' : '2'}`
  return teamClass
}

export const getUniqueTeamClass = (
  roundIndex: number,
  matchIndex: number,
  position: string
) => {
  const className = `wpbb-team-${roundIndex}-${matchIndex}-${position}`
  return className
}

/**
 * Get the match gap for a subsequent round of a bracket given the first round match gap
 * @param {number} firstRoundMatchGap The match height for the first round of a bracket
 * @param {number} matchHeight The height of the match
 * @param {number} i The index of the round when building up from the first round
 */
export const getMatchGap = (
  firstRoundMatchGap: number,
  matchHeight: number,
  i: number
) => {
  const power = 2 ** i
  return firstRoundMatchGap * power + (power - 1) * matchHeight
}

/**
 * Bitwise operation to check if a number is a power of 2
 */
export const isPowerOfTwo = (num: number) => {
  return (num & (num - 1)) === 0
}

/**
 * Return the current round's match gap given the previous round's match height and gap
 */
export const getSubsequentMatchGap = (
  prevMatchHeight: number,
  prevMatchGap: number,
  matchHeight: number
) => {
  return 2 * (prevMatchHeight + prevMatchGap) - matchHeight
}

export const getBracketMeta = (bracket?: BracketRes): BracketMeta => {
  if (!bracket) {
    return null
  }
  const { title, month, year, isOpen } = bracket
  const date = [month, year].filter(Boolean).join(' ')
  return { title, date, isOpen }
}

export const someMatchNotPicked = (matches: MatchNode[]) => {
  const notPicked = matches.some((match) => match && !match.isPicked())
  return notPicked
}

export const defaultTeamClickDisabledCallback = (
  match: MatchNode,
  position: string,
  team: Team
) => {
  return team ? false : true
}
