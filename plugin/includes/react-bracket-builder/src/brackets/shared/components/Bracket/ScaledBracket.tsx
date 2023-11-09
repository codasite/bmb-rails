import { useState, useEffect, useMemo, useContext } from 'react'
import { ScaledBracketProps } from '../types'
import { getBracketWidth as getWidthDefault } from './utils'
import { WithSizeChangeListeners } from '../HigherOrder/WithSizeChangeListeners'
import { SizeChangeListenerContext } from '../../context/SizeChangeListenerContext'
import { WindowDimensionsContext } from '../../context/WindowDimensionsContext'

const ScaledBracket = (props: ScaledBracketProps) => {
  const {
    BracketComponent,
    getBracketWidth = getWidthDefault,
    matchTree,
    scale = 0.3,
    windowWidth: windowWidthProp,
    paddingX = 20,
  } = props
  let childProps = { ...props, BracketComponent: undefined }

  const { addSizeChangeListener, removeSizeChangeListener } = useContext(
    SizeChangeListenerContext
  )
  const { width: windowWidthContext } = useContext(WindowDimensionsContext)
  const [bracketHeight, setBracketHeight] = useState(0)
  const bracketWidth = getBracketWidth(matchTree.rounds.length)
  const windowWidth = windowWidthProp || windowWidthContext

  useEffect(() => {
    const sizeListener = (height: number, width: number) => {
      if (height !== bracketHeight && height) {
        setBracketHeight(height)
      }
    }

    addSizeChangeListener?.(sizeListener)
    return () => {
      removeSizeChangeListener?.(sizeListener)
    }
  }, [bracketHeight, addSizeChangeListener, removeSizeChangeListener])

  const scaleFactor = useMemo(() => {
    return windowWidth ? (windowWidth - paddingX * 2) / bracketWidth : scale
  }, [windowWidth, bracketWidth, paddingX])
  const scaledBracketHeight = bracketHeight * scaleFactor
  const scaledBracketWidth = bracketWidth * scaleFactor

  return (
    <div
      className={`tw-flex tw-flex-col tw-justify-center tw-items-center`}
      style={{ height: scaledBracketHeight, width: scaledBracketWidth }}
    >
      <div className={``} style={{ transform: `scale(${scaleFactor})` }}>
        <BracketComponent lineWidth={scale} {...childProps} />
      </div>
    </div>
  )
}

const WrappedScaledBracket = WithSizeChangeListeners(ScaledBracket)

export { WrappedScaledBracket as ScaledBracket }
