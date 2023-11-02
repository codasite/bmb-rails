import React, { useEffect } from 'react'
import { ThemeSelector } from '../../shared/components'
import { MatchTree } from '../../shared/models/MatchTree'
import { PickableBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'
//@ts-ignore
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
//@ts-ignore
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { BracketMeta } from '../../shared/context'
import { getBracketMeta } from '../../shared/utils'
import { ViewPlayPageProps } from './types'
import { BusterPlayPage } from './BusterPlayPage'
import { BracketPlayPage } from './BracketPlayPage'

const ViewPlayPage = (props: ViewPlayPageProps) => {
  const { bracketPlay: play } = props
  console.log('ViewPlayPage', props)

  if (!play) {
    return <div>Play not found</div>
  } else if (play.bustedPlay) {
    return <BusterPlayPage {...props} />
  } else {
    return <BracketPlayPage {...props} />
  }
}

const WrappedViewPlayPage = WithProvider(
  WithMatchTree(WithBracketMeta(WithDarkMode(ViewPlayPage)))
)
export default WrappedViewPlayPage
