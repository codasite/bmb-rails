import React, { useEffect } from 'react'
import { BracketMeta, DarkModeContext } from '../../shared/context'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
import {
  WithBracketMeta,
  WithMatchTree,
  WithProvider,
  WithDarkMode,
} from '../../shared/components/HigherOrder'
import { camelCaseKeys } from '../../shared/api/bracketApi'
import { parseParams } from './schema'
import { validateParams } from './validate'
import { PrintParams } from './types'

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
    console.log('PrintPlayPage useEffect')

    const urlParams = new URLSearchParams(window.location.search)
    const parsed = parseParams(urlParams)
    console.log('parsed', parsed)
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
      month,
      year,
      picks,
      matches,
      numTeams,
    } = parsed

    setDarkMode(theme === 'dark')
    setPosition(position)
    setInchHeight(inchHeight)
    setInchWidth(inchWidth)
    setBracketMeta({ title, month, year })

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
  const {
    title: bracketTitle,
    month: bracketMonth,
    year: bracketYear,
  } = bracketMeta

  return (
    <div
      className={`wpbb-reset tw-py-60 tw-mx-auto tw-flex tw-flex-col tw-items-center tw-justify-${justify} tw-h-[${heightPx}px] tw-w-[${widthPx}px]${
        darkMode ? ' tw-dark' : ''
      }`}
    >
      {matchTree && bracketTitle && (
        <PickableBracket
          matchTree={matchTree}
          darkMode={darkMode}
          title={bracketTitle}
          month={bracketMonth}
          year={bracketYear}
        />
      )}
    </div>
  )
}

const WrappedPrintPlayPage = WithProvider(
  WithDarkMode(WithBracketMeta(WithMatchTree(PrintPlayPage)))
)

export default WrappedPrintPlayPage
