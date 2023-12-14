import React, { useEffect } from 'react'
import { BracketMeta } from '../../shared/context/context'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
} from '../../shared/components/HigherOrder'
import { parseParams } from './schema'
import { validateParams } from './validate'
import { ScaledBracket } from '../../shared/components/Bracket/ScaledBracket'

interface PrintBracketPageProps {
  bracketMeta: BracketMeta
  setBracketMeta: (bracketMeta: BracketMeta) => void
  matchTree: MatchTree
  setMatchTree: (matchTree: MatchTree) => void
  darkMode: boolean
  setDarkMode: (darkMode: boolean) => void
}

const PrintPlayPage = (props: PrintBracketPageProps) => {
  const {
    bracketMeta,
    setBracketMeta,
    matchTree,
    setMatchTree,
    darkMode,
    setDarkMode,
  } = props

  const [position, setPosition] = React.useState('top')
  const [inchHeight, setInchHeight] = React.useState(16)
  const [inchWidth, setInchWidth] = React.useState(12)

  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search)
    const parsed = parseParams(urlParams)
    const errors = validateParams(parsed)

    if (errors.length > 0) {
      throw new Error(errors.join(', '))
    }

    const {
      theme,
      position,
      inchHeight,
      inchWidth,
      title,
      date,
      picks,
      matches,
      numTeams,
    } = parsed

    setDarkMode(theme === 'dark')
    setPosition(position)
    setInchHeight(inchHeight)
    setInchWidth(inchWidth)
    setBracketMeta({ title, date })

    const tree = MatchTree.fromPicks(numTeams, matches, picks)
    console.log('tree', tree)

    if (tree) {
      setMatchTree(tree)
    }
  }, [])

  let justify = 'flex-start'

  if (position === 'center') {
    justify = 'center'
  } else if (position === 'bottom') {
    justify = 'flex-end'
  }

  const heightPx = inchHeight * 96
  const widthPx = inchWidth * 96
  const { title: bracketTitle, date: bracketDate } = bracketMeta

  return (
    <div
      className={`wpbb-reset tw-py-60 tw-mx-auto tw-flex tw-flex-col tw-items-center tw-justify-${justify} tw-h-[${heightPx}px] tw-w-[${widthPx}px]${
        darkMode ? ' tw-dark' : ''
      }`}
    >
      {matchTree && bracketTitle && (
        <ScaledBracket
          BracketComponent={PickableBracket}
          matchTree={matchTree}
          darkMode={darkMode}
          title={bracketTitle}
          date={bracketDate}
          windowWidth={widthPx}
          paddingX={20}
        />
      )}
    </div>
  )
}

const WrappedPrintPlayPage = WithDarkMode(
  WithBracketMeta(WithMatchTree(PrintPlayPage))
)

export default WrappedPrintPlayPage
