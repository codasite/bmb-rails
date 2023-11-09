import { useRef, useCallback, useState } from 'react'
import { TeamSlotProps } from './../types'
import {
  getUniqueTeamClass,
  getTeamFontSize,
  getTeamMinFontSize,
} from '../Bracket/utils'
import { useResizeObserver } from '../../../../utils/hooks'

export const BaseTeamSlot = (props: TeamSlotProps) => {
  const {
    team,
    match,
    teamPosition,
    height,
    width: boxWidth = 115,
    fontWeight = 500,
    getFontSize = getTeamFontSize,
    textColor = 'white',
    textPaddingX = 4,
    backgroundColor,
    borderColor,
    borderWidth = 2,
    getTeamClass = getUniqueTeamClass,
    onTeamClick,
    matchTree,
    children,
  } = props
  const [textScale, setTextScale] = useState(1)
  const teamClass = getTeamClass(
    match.roundIndex,
    match.matchIndex,
    teamPosition ? teamPosition : 'left'
  )
  const textRef = useRef(null)

  const resizeCallback = useCallback(({ width: currentWidth }) => {
    const targetWidth = boxWidth - 2 * textPaddingX - 2 * borderWidth
    if (currentWidth <= targetWidth) return
    const scaleFactor = targetWidth / currentWidth
    setTextScale(scaleFactor)
  }, [])

  useResizeObserver(textRef, resizeCallback)

  const fontSizeToUse = getFontSize(matchTree.rounds.length)

  const baseStyles = [
    teamClass,
    'tw-flex',
    'tw-justify-center',
    'tw-items-center',
    'tw-whitespace-nowrap',
    'tw-leading-none', // line height: 1 so that font size can be guaged by scale factor
    `tw-w-[${boxWidth}px]`,
    `tw-h-[${height}px]`,
    `tw-text-${textColor}`,
    `tw-font-${fontWeight}`,
  ]
  if (onTeamClick) {
    baseStyles.push('tw-cursor-pointer')
  }
  if (backgroundColor) {
    baseStyles.push(`tw-bg-${backgroundColor}`)
  }
  if (borderColor) {
    baseStyles.push(...['tw-border-solid', `tw-border-${borderColor}`])
  }

  const styles = baseStyles.join(' ')

  const handleTeamClick = () => {
    if (onTeamClick) {
      onTeamClick(match, teamPosition, team)
    }
  }

  return (
    <div
      className={styles}
      onClick={handleTeamClick}
      style={{ borderWidth: borderWidth }}
    >
      {children ? (
        children
      ) : (
        <span
          className={`tw-font-${fontWeight}`}
          style={{
            fontSize: fontSizeToUse,
            transform: `scale(${textScale})`,
          }}
          ref={textRef}
        >
          {team ? team.name : ''}
        </span>
      )}
    </div>
  )
}
