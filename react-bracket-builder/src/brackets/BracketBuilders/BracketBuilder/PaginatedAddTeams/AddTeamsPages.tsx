// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { BracketPagesProps } from '../../PaginatedBuilderBase/types'
import { PaginatedAddTeamsBracket } from '../../../shared/components/Bracket/PaginatedAddTeamsBracket'

export const AddTeamsPages = (props: BracketPagesProps) => {
  const { matchTree, setMatchTree, onFinished } = props

  return (
    <PaginatedAddTeamsBracket
      matchTree={matchTree}
      setMatchTree={setMatchTree}
      onFinished={onFinished}
    />
  )
}
