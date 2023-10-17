import React from 'react'
import { PaginatedBracketProps } from '../types'
import { PickableBracket } from './PickableBracket'
import { PaginatedDefaultBracket } from './PaginatedDefaultBracket'

export const PaginatedPickableBracket = (props: PaginatedBracketProps) => {
  const { matchTree, setMatchTree } = props

  console.log('PaginatedPickableBracket', matchTree)
  return (
    <PickableBracket BracketComponent={PaginatedDefaultBracket} {...props} />
  )
}
