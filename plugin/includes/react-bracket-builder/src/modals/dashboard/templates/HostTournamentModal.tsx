import * as React from 'react'
import { useState } from 'react'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import { TextFieldModal } from '../../TextFieldModal'
import addClickHandlers from '../../addClickHandlers'

export const HostBracketModal = (props: { bracketsUrl: string }) => {
  const [bracketId, setBracketId] = useState<number | null>(null)
  const [bracketDate, setBracketDate] = useState<string>('')
  const [loading, setLoading] = useState(false)
  const [input, setInput] = useState('')
  const [hasError, setHasError] = useState(false)
  const [show, setShow] = useState(false)
  addClickHandlers({
    buttonClassName: 'wpbb-host-bracket-button',
    onButtonClick: (b) => {
      setBracketId(parseInt(b.dataset.bracketId))
      setBracketDate(b.dataset.bracketDate)
      setShow(true)
    },
  })
  const onHostBracket = () => {
    if (!input) {
      setHasError(true)
      return
    }
    setLoading(true)
    bracketApi
      .createBracket({
        bracketBracketId: bracketId,
        title: input,
        date: bracketDate,
      })
      .then((res) => {
        window.location.href = props.bracketsUrl
      })
      .catch((err) => {
        console.error(err)
        setLoading(false)
      })
  }
  return (
    <TextFieldModal
      submitButtonText={'Host'}
      onSubmit={onHostBracket}
      header={'Host bracket'}
      input={input}
      setInput={setInput}
      hasError={hasError}
      setHasError={setHasError}
      loading={loading}
      errorText={'Bracket name is required'}
      placeholderText={'Bracket name...'}
      setShow={setShow}
      show={show}
    />
  )
}
