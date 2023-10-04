export const ModalHeader = (props: { text: string }) => {
  return (
    <h1 className="tw-text-32 tw-leading-10 tw-font-white tw-whitespace-pre-line tw-mb-30 tw-w-full tw-text-center">
      {props.text}
    </h1>
  )
}
