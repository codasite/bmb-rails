// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { BracketPagesProps } from '../../PaginatedBuilderBase/types'
import { PaginatedAddTeamsBracket } from '../../../shared/components/Bracket/PaginatedAddTeamsBracket'
import { hasNeededTeams } from '../../../shared/models/operations/HasNeededTeams'

export const AddTeamsPages = (props: BracketPagesProps) => {
  const { matchTree, setMatchTree, onFinished } = props

  return (
    <PaginatedAddTeamsBracket
      matchTree={matchTree}
      setMatchTree={setMatchTree}
      onFinished={onFinished}
      disableNext={(visibleMatches) => {
        return visibleMatches.some((match) => {
          return !hasNeededTeams(match)
        })
      }}
      hasNext={(matchTree, currentPage) => {
        if (matchTree.rounds.length < 2) {
          return false
        }
        if (currentPage === 0) {
          return true
        }
        const secondRoundMatches = matchTree.rounds[1].matches
        if (currentPage === 1) {
          return secondRoundMatches.some((match) => match.hasLeafTeam())
        }
        if (currentPage === 2) {
          const mid = Math.floor(secondRoundMatches.length / 2)
          return secondRoundMatches
            .slice(mid)
            .some((match) => match.hasLeafTeam())
        }
        return false
      }}
    />
  )
}
