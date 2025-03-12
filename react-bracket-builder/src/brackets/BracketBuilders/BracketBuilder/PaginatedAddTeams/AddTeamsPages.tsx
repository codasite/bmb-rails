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
      // hasNext={(matchTree, currentPage) => {
      //   return false
      // }}
    />
  )
}
