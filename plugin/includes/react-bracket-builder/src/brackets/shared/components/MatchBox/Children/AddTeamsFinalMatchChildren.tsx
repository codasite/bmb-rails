import React, { useContext } from 'react'
import { MatchBoxChildProps } from '../../types'
//@ts-ignore
import { BracketMetaContext } from '../../../context'
import { WinnerContainer } from './WinnerContainer'
import { LogoContainer } from './LogoContainer'

export const AddTeamsFinalMatchChildren = (props: MatchBoxChildProps) => {
  const { matchPosition } = props

  const { date: bracketDate, title: bracketTitle } =
    useContext(BracketMetaContext)

  return matchPosition === 'center' ? (
    <>
      <WinnerContainer topText={bracketTitle} topTextFontSize={48} {...props} />
      <LogoContainer
        {...props}
        topTextColorDark="white/50"
        logoColorDark="white/50"
        bottomText={bracketDate}
        bottomTextColorDark="white/50"
        bottomTextFontSize={24}
      />
    </>
  ) : (
    <></>
  )
}
