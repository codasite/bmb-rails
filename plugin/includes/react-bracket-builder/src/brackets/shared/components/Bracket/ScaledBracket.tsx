import React, { useContext } from 'react'
import { ScaledBracketProps } from '../types'
import { DarkModeContext } from '../../context'

export const ScaledBracket = (props: ScaledBracketProps) => {
  const {
    BracketComponent,
    getBracketHeight = () => 350,
    matchTree,
    scale = 0.3,
    lineStyle,
  } = props

  delete props.BracketComponent

  const height = getBracketHeight(matchTree.rounds.length)
  console.log('ScaledBracket', height, scale)

  const darkMode = useContext(DarkModeContext)

  return (
    <div
      className={`tw-flex tw-flex-col tw-justify-center tw-items-center tw-h-[${height}px]`}
    >
      <div className={`tw-scale-${scale * 100}`}>
        <BracketComponent lineWidth={scale} {...props} />
      </div>
    </div>
  )
}
