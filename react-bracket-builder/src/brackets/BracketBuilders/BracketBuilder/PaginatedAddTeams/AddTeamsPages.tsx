// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { BracketPagesProps } from '../../PaginatedBuilderBase/types'
import { PaginatedAddTeamsBracketNew } from '../../../shared/components/Bracket/PaginatedAddTeamsBracketNew'

export const AddTeamsPages = (props: BracketPagesProps) => {
  const { matchTree, setMatchTree, onFinished } = props

  return (
    <PaginatedAddTeamsBracketNew
      matchTree={matchTree}
      setMatchTree={setMatchTree}
      onFinished={onFinished}
    />
  )
}
