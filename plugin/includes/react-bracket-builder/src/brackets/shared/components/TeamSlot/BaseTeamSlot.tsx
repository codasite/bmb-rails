import { TeamSlotProps } from './../types'
import {
  getUniqueTeamClass,
  getTeamFontSize,
  defaultTeamClickDisabledCallback,
  getTeamPaddingX,
} from '../Bracket/utils'
import { ScaledSpan } from './ScaledSpan'

const DivOrButton = (props: any) => {
  const { onClick, onFocus, ...rest } = props
  if (onClick || onFocus) {
    return <button onClick={onClick} onFocus={onFocus} {...rest} />
  } else {
    return <div {...rest} />
  }
}

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
    getPaddingX = getTeamPaddingX,
    backgroundColor = 'transparent',
    borderColor,
    borderWidth = 0,
    getTeamClass = getUniqueTeamClass,
    onTeamClick,
    onTeamFocus,
    matchTree,
    children,
    placeholder = '',
    teamClickDisabled = defaultTeamClickDisabledCallback,
  } = props
  const teamClass = getTeamClass(
    match.roundIndex,
    match.matchIndex,
    teamPosition ? teamPosition : 'left'
  )
  const paddingX = getPaddingX(matchTree.rounds.length)
  const targetWidth = boxWidth - 2 * paddingX - 2 * borderWidth
  const fontSizeToUse = getFontSize(matchTree.rounds.length)

  const handleTeamClick = teamClickDisabled(match, teamPosition, team)
    ? undefined
    : () => onTeamClick(match, teamPosition, team)

  const baseStyles = [
    teamClass,
    'tw-flex',
    'tw-justify-center',
    'tw-items-center',
    'tw-whitespace-nowrap',
    'tw-leading-none', // line height: 1 so that font size can be guaged by scale factor
    'tw-uppercase',
    'tw-font-sans',
    `tw-w-[${boxWidth}px]`,
    `tw-h-[${height}px]`,
    `tw-text-${textColor}`,
    `tw-font-${fontWeight}`,
  ]
  if (backgroundColor) {
    baseStyles.push(`tw-bg-${backgroundColor}`)
  }
  if (borderColor && borderWidth > 0) {
    baseStyles.push(...['tw-border-solid', `tw-border-${borderColor}`])
  }
  if (handleTeamClick) {
    baseStyles.push('tw-cursor-pointer')
  }

  const styles = baseStyles.join(' ')

  return (
    <DivOrButton
      className={styles}
      onClick={handleTeamClick}
      onFocus={onTeamFocus}
      style={{ borderWidth: borderWidth }}
    >
      {children
        ? children
        : (team?.name || placeholder) && (
            <ScaledSpan
              style={{ fontSize: fontSizeToUse }}
              targetWidth={targetWidth}
            >
              {team?.name || placeholder}
            </ScaledSpan>
          )}
    </DivOrButton>
  )
}
