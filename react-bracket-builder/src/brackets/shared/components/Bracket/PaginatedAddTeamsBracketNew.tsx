// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React from 'react'
import { PaginatedBracketProps } from '../types'
import { AddTeamsBracket } from './AddTeamsBracket'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'

export const PaginatedAddTeamsBracketNew = (props: PaginatedBracketProps) => {
  return (
    <AddTeamsBracket {...props} BracketComponent={PaginatedDefaultBracket} />
  )
}
