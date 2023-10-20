import React, { useEffect, useState } from 'react'
import * as Sentry from '@sentry/react'
import { bracketApi } from '../../shared/api/bracketApi'
import { Nullable } from '../../../utils/types'
import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'
import { BracketRes, PlayReq } from '../../shared/api/types/bracket'
import { useWindowDimensions } from '../../../utils/hooks'
import { PaginatedPlayBuilder } from './PaginatedPlayBuilder/PaginatedPlayBuilder'
import { PlayBuilder } from './PlayBuilder'
import { getBracketWidth } from '../../shared/utils'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'

interface PlayPageProps {
  apparelUrl: string
  bracketStylesheetUrl: string
  bracket?: BracketRes
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
}

const PlayPage = (props: PlayPageProps) => {
  console.log('PlayPage')
  const {
    bracket,
    apparelUrl,
    bracketStylesheetUrl,
    matchTree,
    setMatchTree,
    bracketMeta,
    setBracketMeta,
    darkMode,
    setDarkMode,
  } = props

  const [processing, setProcessing] = useState(false)
  const { width: windowWidth, height: windowHeight } = useWindowDimensions()
  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(bracket?.numTeams))

  useEffect(() => {
    let tree: Nullable<MatchTree> = null
    if (bracket) {
      const numTeams = bracket.numTeams
      const matches = bracket.matches
      tree = MatchTree.fromMatchRes(numTeams, matches)
      setBracketMeta?.({ title: bracket.title, month: bracket.month, year: bracket.year })
    }
    if (tree && setMatchTree) {
      setMatchTree(tree)
    }
  }, [])

  const buildPrintHTML = (
    innerHTML: string,
    styleUrl: string,
    inchHeight: number,
    inchWidth: number
  ) => {
    const printArea = buildPrintArea(innerHTML, inchHeight, inchWidth)
    // const stylesheet = 'https://backmybracket.com/wp-content/plugins/wp-bracket-builder/includes/react-bracket-builder/build/index.css'
    const stylesheet = 'https://wpbb-stylesheets.s3.amazonaws.com/index.css'
    return `
			<html>
				<head>
					<link rel='stylesheet' href='${stylesheet}' />
				</head>
			<body style='margin: 0; padding: 0;'>
				${printArea}
			</body>
			</html>
		`
  }

  const buildPrintArea = (
    innerHTML: string,
    inchHeight: number,
    inchWidth: number
  ) => {
    const width = inchWidth * 96
    const height = inchHeight * 96
    return `
			<div class='wpbb-bracket-print-area' style='height: ${height}px; width: ${width}px; background-color: transparent'>
				${innerHTML}
			</div>
		`
  }

  const getHTML = (): string => {
    const bracketEl = document.getElementsByClassName('wpbb-bracket')[0]
    const bracketHTML = bracketEl.outerHTML
    const bracketCss = bracketStylesheetUrl
    const html = buildPrintHTML(bracketHTML, bracketCss, 16, 12)
    return html
  }

  const minify = (html: string) => {
    return html.replace(/[\n\t]/g, '').replace(/"/g, "'")
  }

  const handleApparelClick = () => {
    console.log('handleApparelClick')
    const picks = matchTree?.toMatchPicks()
    console.log(picks)
    const bracketId = bracket?.id
    console.log(bracketId)
    if (!picks) {
      const msg = 'Cannot create play. Missing picks'
      console.error(msg)
      Sentry.captureException(msg)
      return
    }
    const playReq: PlayReq = {
      bracketId: bracket?.id,
      picks: picks,
      generateImages: true,
    }

    console.log(playReq)
    setProcessing(true)
    bracketApi
      .createPlay(playReq)
      .then((res) => {
        console.log(res)
        // window.location.href = apparelUrl
      })
      .catch((err) => {
        console.error('error: ', err)
        Sentry.captureException(err)
      })
      .finally(() => {
        setProcessing(false)
      })
  }

  const playBuilderProps = {
    matchTree,
    setMatchTree,
    handleApparelClick,
    processing,
    darkMode,
    setDarkMode,
    bracketMeta,
    setBracketMeta,
  }

  if (showPaginated) {
    return <PaginatedPlayBuilder {...playBuilderProps} />
  }

  return <PlayBuilder {...playBuilderProps} />
}

const WrappedPlayPage = WithProvider(
  WithDarkMode(WithMatchTree(WithBracketMeta(PlayPage)))
)

export default WrappedPlayPage
