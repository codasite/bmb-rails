// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useContext } from 'react'
import { MatchBoxChildProps } from '../../types'
//@ts-ignore
import { BracketMetaContext } from '../../../context/context'
import { WinnerContainer } from './WinnerContainer'
import { LogoContainer } from './LogoContainer'

export const DefaultFinalMatchChildren = (props: MatchBoxChildProps) => {
  const { matchPosition } = props

  const { bracketMeta } = useContext(BracketMetaContext)
  const { title: bracketTitle, date: bracketDate } = bracketMeta

  return matchPosition === 'center' ? (
    <>
      <WinnerContainer {...props} title={bracketTitle} />
      <LogoContainer {...props} bottomText={bracketDate} />
    </>
  ) : (
    <></>
  )
}
